<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:99:"/Applications/xampp/xamppfiles/htdocs/huachuang/public/../application/admin/view/appconf/index.html";i:1548323204;}*/ ?>
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
	<!--[if IE 6]>
	<script type="text/javascript" src="/static/lib/DD_belatedPNG_0.0.8a-min.js" ></script>
	<script>DD_belatedPNG.fix('*');</script>
	<![endif]-->
	<title>app配置</title>
	<style type="text/css">
		.dataTables_info {clear: both;float: left;padding-top: 10px;font-size: 14px;color: #666;}
		.pagination{float: right;padding-top: 10px;text-align: right;}
		.pagination>li{border: 1px solid #ccc;cursor: pointer;display: inline-block;margin-left: 2px;text-align: center;text-decoration: none;color: #666;height: 26px;line-height: 26px;text-decoration: none;margin: 0 0 6px 6px;padding: 0 10px;font-size: 14px;}
	</style>
</head>
<body>
	<nav class="breadcrumb">
		<i class="Hui-iconfont">&#xe67f;</i> 首页 
		<span class="c-gray en">&gt;</span> 系统管理 
		<span class="c-gray en">&gt;</span> app配置 
		<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" >
			<i class="Hui-iconfont">&#xe68f;</i>
		</a>
	</nav>
	<div class="page-container">
		<table class="table table-border table-bordered table-bg">
			<thead>
				<tr>
					<th scope="col" colspan="11">详细列表</th>
				</tr>
				<tr class="text-c">
					<th width="25"><input type="checkbox" name="" value=""></th>
					<th width="100">app名字</th>
					<th width="100">id名字</th>
					<th width="100">货币名字</th>
					<th width="100">提现货币名字</th>
					<th width="100">操作</th>
				</tr>
			</thead>
			<tbody>
				<?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
				<tr class="text-c">
					<td><input type="checkbox" value="<?php echo $vo['id']; ?>" name=""></td>
					<td><?php echo $vo['app_name']; ?></td>
					<td><?php echo $vo['id_name']; ?></td>
					<td><?php echo $vo['currency_name']; ?></td>
					<td><?php echo $vo['cash_name']; ?></td>
					<td>
						<?php if(( action('Base/is_power','/admin/appconf/edit.html') )): ?>
						<a title="编辑" href="javascript:;" onclick="admin_permission_edit('菜单编辑','/admin/appconf/edit.html?id=<?php echo $vo['id']; ?>','','')" class="ml-5" style="text-decoration:none">
							<i class="Hui-iconfont">&#xe6df;</i>
						</a> 
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; endif; else: echo "" ;endif; ?>
			</tbody>
		</table>
	</div>
	<!--_footer 作为公共模版分离出去-->
	<script type="text/javascript" src="/static/lib/jquery/1.9.1/jquery.min.js"></script> 
	<script type="text/javascript" src="/static/lib/layer/2.4/layer.js"></script>
	<script type="text/javascript" src="/static/h-ui/js/H-ui.min.js"></script> 
	<script type="text/javascript" src="/static/h-ui.admin/js/H-ui.admin.js"></script> 
	<!--/_footer 作为公共模版分离出去-->

	<!--请在下方写此页面业务相关的脚本-->
	<script type="text/javascript" src="/static/lib/datatables/1.10.0/jquery.dataTables.min.js"></script> 
	<script type="text/javascript">
		/*管理员-权限-编辑*/
		function admin_permission_edit(title,url,w,h){
			layer_show(title,url,w,h);
		}
	</script>
</body>
</html>