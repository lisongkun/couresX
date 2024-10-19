<?php

include ('../confing/common.php');
// 创建 Redis 连接
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$codeType = 'plbsoid';// 任务唯一标识

$logFilePath = __DIR__ . '/logs/plbs_log.txt';

// 获取当前时间
function getTimeStamp()
{
    return DateTime::createFromFormat('U.u', microtime(true))->setTimezone(new DateTimeZone('Asia/Shanghai'))->format("Y-m-d H:i:s--u");
}

// 写入日志
function writeLog($logFilePath, $logContent)
{
    global $logFilePath;
    
   // 尝试获取文件锁
    $fileHandle = fopen($logFilePath, 'a'); // 以追加方式打开文件
    if ($fileHandle === false) {
        echo "无法打开日志文件";
        return;
    }

    if (flock($fileHandle, LOCK_EX)) { // 获取排它锁
        // 如果日志文件不存在，则创建
        if (!file_exists($logFilePath)) {
            touch($logFilePath); // 创建文件
        }

        // 获取当前日志文件大小
        $fileSize = filesize($logFilePath);

        // 如果日志文件大小大于20MB
        if ($fileSize > 20 * 1024 * 1024) {
            // 读取文件内容
            $oldLog = file_get_contents($logFilePath);
            // 截取前20MB内容
            $newLog = mb_substr($oldLog, -$fileSize + 20 * 1024 * 1024, null, 'UTF-8');
            // 写入新日志内容
            file_put_contents($logFilePath, $logContent . PHP_EOL . $newLog);
        } else {
            // 直接将新日志内容追加到文件开头
            file_put_contents($logFilePath, $logContent . PHP_EOL . file_get_contents($logFilePath));
        }

        // 释放文件锁
        flock($fileHandle, LOCK_UN);
    } else {
        echo "无法获取文件锁";
    }

    // 关闭文件句柄
    fclose($fileHandle);

    // 输出日志内容
    echo $logContent;
}


// 整理日志
function formatLog($oid, $message, $additional = '')
{
    $pid = getmypid();
    $uptime = getTimeStamp();
    return "【" . $pid . "】" . "订单ID：{$oid} {$message}\r\n {$additional} 出队时间：{$uptime}\r\n----------------------------------------\r\n";
}

// 加锁函数
function acquireTaskLock($redis, $taskId, $timeout = 10)
{
    global $codeType;
    $identifier = uniqid(); // 生成唯一标识符
    $lockKey = $codeType . "_taskLock:$taskId";

    // 尝试获取锁
    $acquired = $redis->set($lockKey, $identifier, ['nx', 'ex' => $timeout]);

    // 返回锁的标识符或 false（获取失败）
    return $acquired ? $identifier : false;
}

// 释放锁函数
function releaseTaskLock($redis, $taskId, $identifier)
{
    global $codeType;
    $lockKey = $codeType . "_taskLock:$taskId";
    $lockValue = $redis->get($lockKey);

    // 如果当前锁的标识符与传入的标识符相同，则释放锁
    if ($lockValue == $identifier) {
        $redis->del($lockKey);
    }
}

// 获取任务
function getTaskFromQueue($redis)
{
    global $codeType;
    // 从队列中获取任务...
    $oid = $redis->blpop($codeType, 0);
    return $oid[1];
}

// 处理任务
function processTask($taskId)
{
    global $redis;
    global $codeType;
    global $DB;
    $pid = getmypid();// 获取当前进程PID

    $oid = $taskId;
    if (!empty($oid)) {
        $oid = trim($oid);
            $result = budanWk($oid);

            if ($result["code"] === 404) {
                $logContent = formatLog($oid, "失败，上游通讯异常");
                writeLog($logFilePath, $logContent);
                return;
            }

            if ($result["code"] === -1) {
                $logContent = formatLog($oid, "失败，" . $result["msg"]);
                writeLog($logFilePath, $logContent);
                return;
            }

            if ($result['code'] == 1) {
                $status = '补刷中';
                $msgType = "[1]";
            } else {
                $status = '补刷异常';
                $msgType = "[2]";
            }

            $uptime = getTimeStamp();
            $ok = $DB->query("UPDATE qingka_wangke_order SET status='{$status}', `bsnum`=bsnum+1,`uptime`='{$uptime}' WHERE oid='{$oid}'AND `yid`='{$res['yid']}'");

            if ($ok) {
                $logContent = formatLog($oid, "成功！{$msgType}\r\n信息：{$result['msg']}\r\n出队时间: {$uptime}");
                writeLog($logFilePath, $logContent);
            } else {
                $logContent = formatLog($oid, "失败！{$msgType}\r\n信息：{$result['msg']}\r\n出队时间: {$uptime}");
                writeLog($logFilePath, $logContent);
            }
        
    } else {
    }

}

// 获取锁
while ($taskId = getTaskFromQueue($redis)) {
    $lockIdentifier = acquireTaskLock($redis, $taskId);

    if ($lockIdentifier !== false) {
        // 成功获取锁，可以进行队列处理操作
        processTask($taskId);

        // 释放锁
        releaseTaskLock($redis, $taskId, $lockIdentifier);
        sleep(2);
    } else {
        // 获取锁失败，可以选择等待一段时间或直接放弃处理
        echo "获取锁失败\r\n----------------------------------------\r\n";
    }
}

?>