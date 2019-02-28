<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:101:"/Applications/xampp/xamppfiles/htdocs/huachuang/public/../application/admin/view/sociality/index.html";i:1548662346;}*/ ?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<meta name="renderer" content="webkit|ie-comp|ie-stand">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
	<meta http-equiv="Cache-Control" content="no-siteapp" />
	<!--[if lt IE 9]>
	<script type="text/javascript" src="/static/lib/html5shiv.js"></script>
	<script type="text/javascript" src="/static/lib/respond.min.js"></script>
	<![endif]-->
	<link rel="stylesheet" type="text/css" href="/static/h-ui/css/H-ui.min.css" />
	<link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/H-ui.admin.css" />
	<link rel="stylesheet" type="text/css" href="/static/lib/Hui-iconfont/1.0.8/iconfont.css" />
	<link rel="stylesheet" type="text/css" href="/static/h-ui.admin/skin/default/skin.css" id="skin" />
	<link rel="stylesheet" type="text/css" href="/static/h-ui.admin/css/style.css" />
	<link rel="stylesheet" href="/static/layui/css/layui.css" media="all">
	<!--[if IE 6]>
	<script type="text/javascript" src="/static/lib/DD_belatedPNG_0.0.8a-min.js" ></script>
	<script>DD_belatedPNG.fix('*');</script>
	<![endif]-->
	<title>分类管理</title>
	<style type="text/css">
		.dataTables_info {clear: both;float: left;padding-top: 10px;font-size: 14px;color: #666;}
		.pagination{float: right;padding-top: 10px;text-align: right;}
		.pagination>li{border: 1px solid #ccc;cursor: pointer;display: inline-block;margin-left: 2px;text-align: center;text-decoration: none;color: #666;height: 26px;line-height: 26px;text-decoration: none;margin: 0 0 6px 6px;padding: 0 10px;font-size: 14px;}
	</style>
</head>
<body>
	<nav class="breadcrumb">
		<i class="Hui-iconfont">&#xe67f;</i> 首页 
		<span class="c-gray en">&gt;</span> 上船管理 
		<span class="c-gray en">&gt;</span> 分类管理 
		<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" >
			<i class="Hui-iconfont">&#xe68f;</i>
		</a>
	</nav>
	<div class="page-container">
		<div class="cl pd-5 bg-1 bk-gray">
			<span class="l">
				<!-- <a href="javascript:;" onclick="datadel()" class="btn btn-danger radius">
					<i class="Hui-iconfont">&#xe6e2;</i> 批量删除
				</a> -->
				<a href="javascript:;" onclick="admin_add('添加分类','/admin/sociality/add.html','800','500')" class="btn btn-primary radius">
					<i class="Hui-iconfont">&#xe600;</i> 添加分类
				</a>
			</span>

			<select name="pid" id="soc_select" onchange="soc_select(this);" style="width:120px;height:30px;margin-left:5px;">
				<option value="">请选择分类</option>
				<?php if(is_array($type) || $type instanceof \think\Collection || $type instanceof \think\Paginator): $i = 0; $__LIST__ = $type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
				<option value="<?php echo $item['id']; ?>"><?php echo $item['type_name']; ?></option>
				<?php endforeach; endif; else: echo "" ;endif; ?>
			</select>

			<span class="r">共有数据：<strong><?php echo $total; ?></strong> 条</span>
		</div>
		<table class="table table-border table-bordered table-bg">
			<thead>
				<tr>
					<th scope="col" colspan="11">详细列表</th>
				</tr>
				<tr class="text-c">
					<th width="25">
						<input type="checkbox" name="" value="">
					</th>
					<th width="40">序号</th>
					<th width="100">分类名</th>
					<th width="150">图标</th>
					<th width="100">排序号</th>
					<th width="130">创建时间</th>
					<th width="100">操作</th>
				</tr>
			</thead>
			<tbody id="datas">
				<?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $k = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($k % 2 );++$k;?>
				<tr class="text-c">
					<td><input type="checkbox" value="" name=""></td>
					<td><?php echo $k; ?></td>
					<td><?php echo $item['type_name']; ?></td>
					<td><img src="/<?php echo $item['icon']; ?>" style="width:50px;height:50px;"/></td>
					<td><?php echo $item['order']; ?></td>
					<td><?php echo $item['ctime']; ?></td>
					<td class="td-manage">
						<a title="编辑" href="javascript:;" onclick="admin_edit('修改banner','<?php echo url('sociality/edit'); ?>?id=<?php echo $item['id']; ?>&pid=<?php echo $item['pid']; ?>','1','800','500')" class="ml-5" style="text-decoration:none">
							<i class="Hui-iconfont">&#xe6df;</i>
						</a>
						<a title="删除" href="javascript:;" onclick="admin_del(this,'')" class="ml-5" style="text-decoration:none">
							<i class="Hui-iconfont">&#xe6e2;</i>
						</a>
					</td>
				</tr>
				<?php endforeach; endif; else: echo "" ;endif; ?>
			</tbody>
		</table>

		<div id="pages"></div>

	</div>
	<!--_footer 作为公共模版分离出去-->
	<script type="text/javascript" src="/static/lib/jquery/1.9.1/jquery.min.js"></script> 
	<script type="text/javascript" src="/static/lib/layer/2.4/layer.js"></script>
	<script type="text/javascript" src="/static/h-ui/js/H-ui.min.js"></script> 
	<script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.js"></script> <!--/_footer 作为公共模版分离出去-->
	<!--请在下方写此页面业务相关的脚本-->
	<script type="text/javascript" src="/static/lib/My97DatePicker/4.8/WdatePicker.js"></script> 
	<script type="text/javascript" src="/static/lib/datatables/1.10.0/jquery.dataTables.min.js"></script> 
	<script type="text/javascript" src="/static/lib/laypage/1.2/laypage.js"></script>
	<script type="text/javascript" src="/static/lib/jquery.validation/1.14.0/jquery.validate.js"></script> 
	<script type="text/javascript" src="/static/lib/jquery.validation/1.14.0/validate-methods.js"></script> 
	<script type="text/javascript" src="/static/lib/jquery.validation/1.14.0/messages_zh.js"></script>
	<script src="/static/layui/layui.js" ></script>
	<script type="text/javascript">

		layui.use('laypage',function(){
			var laypage = layui.laypage;

			laypage.render({
				elem:'pages',
				limit:10,
				count:21,
				layout:['count','page'],
				jump: function(obj,first) {
					if(!first){
						$.ajax({
							url:'<?php echo url("sociality/addList"); ?>',
							data:{curr:obj.curr},
							type:'post',
							success:function(org){
								org = JSON.parse(org);
								var router = 'sociality/edit';
								var str = '';
								$('#datas').empty();
								for(var i=0;i<org.length;i++){
									str += '<tr class="text-c"><td><input type="checkbox" value="1" name=""></td><td>'+ i +'</td><td>'+ org[i]['type_name'] +'</td><td><img src="/'+ org[i]['icon'] +'" style="width:50px;height:50px;"/></td><td>'+ org[i]['order'] +'</td><td>'+ org[i]['ctime'] +'</td><td class="td-manage"> <a title="编辑" href="javascript:;" onclick="admin_edit(\'修改banner\',\'<?php echo url('sociality/edit'); ?>?id='+ org[i]['id'] +'&pid='+ org[i]['pid'] +' \',\'1\',\'800\',\'500\')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i> </a> <a title="删除" href="javascript:;" onclick="admin_del(this,)" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i> </a> </td></tr>';
								}
								$('#datas').append(str);
							}
						});

					}

				}
			})


		});

		/*
			参数解释：
			title	标题
			url		请求的url
			id		需要操作的数据id
			w		弹出层宽度（缺省调默认值）
			h		弹出层高度（缺省调默认值）
		*/
		/*管理员-增加*/
		function admin_add(title,url,w,h){
			layer_show(title,url,w,h);
		}
		/*管理员-删除*/
		function admin_del(obj,id){
			layer.confirm('确认要删除吗？',function(index){
				$.ajax({
					type: 'POST',
					url: '/admin/banner/del.html',
					data:{id:id},
					dataType: 'json',
					success: function(data){
						if (data.status == 200) {
							$(obj).parents("tr").remove();
							layer.msg(data.msg,{icon:1,time:1000});
						}else{
							layer.msg(data.msg,{icon:2,time:1000});
						}
					},
					error:function(data) {
						alert(data.msg);
					},
				});
			});
		}

		function soc_select(obj){
			var id = obj.value;
			$.ajax({
				url:"<?php echo url('sociality/addList'); ?>",
				data:{id:id,ttype:'checkclass'},
				type:'post',
				success:function(org){
					console.log(org);
				}

			})
		}

		/*管理员-编辑*/
		function admin_edit(title,url,id,w,h){
			layer_show(title,url,w,h);
		}
		/*批量删除*/
		function datadel(){
			layer.msg('未开通',{icon: 4,time:1000});
		}

	</script>
</body>
</html>