layui.define(["table","form"],function(e){var t=layui.$,i=layui.table;layui.form;i.render({elem:"#LAY-app-forum-list",url:layui.setter.base+"json/forum/list.js",cols:[[{type:"checkbox",fixed:"left"},{field:"id",width:100,title:"ID",sort:!0},{field:"poster",title:"\u53d1\u5e16\u4eba"},{field:"avatar",title:"\u5934\u50cf",width:100,templet:"#imgTpl"},{field:"content",title:"\u53d1\u5e16\u5185\u5bb9"},{field:"posttime",title:"\u53d1\u5e16\u65f6\u95f4",sort:!0},{field:"top",title:"\u7f6e\u9876",templet:"#buttonTpl",minWidth:80,align:"center"},{title:"\u64cd\u4f5c",width:150,align:"center",fixed:"right",toolbar:"#table-forum-list"}]],page:!0,limit:10,limits:[10,15,20,25,30],text:"\u5bf9\u4e0d\u8d77\uff0c\u52a0\u8f7d\u51fa\u73b0\u5f02\u5e38\uff01"}),i.on("tool(LAY-app-forum-list)",function(e){e.data;if("del"===e.event)layer.confirm("\u786e\u5b9a\u5220\u9664\u6b64\u6761\u5e16\u5b50\uff1f",function(t){e.del(),layer.close(t)});else if("edit"===e.event){t(e.tr);layer.open({type:2,title:"\u7f16\u8f91\u5e16\u5b50",content:"../../../views/app/forum/listform.html",area:["550px","400px"],btn:["\u786e\u5b9a","\u53d6\u6d88"],resize:!1,yes:function(e,t){var l=window["layui-layer-iframe"+e],r="LAY-app-forum-submit",o=t.find("iframe").contents().find("#"+r);l.layui.form.on("submit("+r+")",function(t){t.field;i.reload("LAY-app-forum-list"),layer.close(e)}),o.trigger("click")},success:function(e,t){}})}}),i.render({elem:"#LAY-app-forumreply-list",url:layui.setter.base+"json/forum/replys.js",cols:[[{type:"checkbox",fixed:"left"},{field:"id",width:100,title:"ID",sort:!0},{field:"replyer",title:"\u56de\u5e16\u4eba"},{field:"cardid",title:"\u56de\u5e16ID",sort:!0},{field:"avatar",title:"\u5934\u50cf",width:100,templet:"#imgTpl"},{field:"content",title:"\u56de\u5e16\u5185\u5bb9",width:200},{field:"replytime",title:"\u56de\u5e16\u65f6\u95f4",sort:!0},{title:"\u64cd\u4f5c",width:150,align:"center",fixed:"right",toolbar:"#table-forum-replys"}]],page:!0,limit:10,limits:[10,15,20,25,30],text:"\u5bf9\u4e0d\u8d77\uff0c\u52a0\u8f7d\u51fa\u73b0\u5f02\u5e38\uff01"}),i.on("tool(LAY-app-forumreply-list)",function(e){e.data;if("del"===e.event)layer.confirm("\u786e\u5b9a\u5220\u9664\u6b64\u6761\u8bc4\u8bba\uff1f",function(t){e.del(),layer.close(t)});else if("edit"===e.event){t(e.tr);layer.open({type:2,title:"\u7f16\u8f91\u8bc4\u8bba",content:"../../../views/app/forum/replysform.html",area:["550px","350px"],btn:["\u786e\u5b9a","\u53d6\u6d88"],resize:!1,yes:function(t,i){var l=i.find("iframe").contents().find("#layuiadmin-form-replys"),r=l.find('textarea[name="content"]').val();e.update({content:r}),layer.close(t)},success:function(e,t){}})}}),e("forum",{})});