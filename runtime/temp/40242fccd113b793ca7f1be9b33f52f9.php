<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:95:"/Applications/xampp/xamppfiles/htdocs/huachuang/public/../application/gzh/view/login/index.html";i:1551085109;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <title>登陆</title>
    <style>
        *{margin:0 auto;padding: 0;}
        body{background-color:#eee;padding: 20px 0;}
        div.login>div.name{padding: 10px 0;background-color:white;border-radius: 5px;width: 94%;box-sizing: border-box;}
        div.login>div.name:not(:first-child){margin-top:15px;}
        div.login>div.name>span{width: 15%;display: inline-block;position: relative;left: 7%;top:5px;}
        div.login>div.name>span>img{height: 20px;}
        div.login>div.name>input.username{line-height: 35px;width: 80%;outline: none;border:0;font-size: 15px;}
        div.login>div.name>input.password{line-height: 35px;width: 40%;outline: none;border:0;font-size: 15px;}
        div.login>div.name>span.ver{display: inline-block;width: 30%;border:1px solid #000fff;line-height: 25px;text-align: center;position: relative;top:0;border-radius: 4px;color:#000fff;}

        div.login>div.tips-text{width: 94%;font-size: 13px;color:#666;text-align: center;margin-top:5px;display: none;}

        div.btn-div{width: 94%;margin-top:35px;}
        div.btn-div>input.btn-button{width: 100%;display: block;background: #000fff;color:white;line-height: 40px;font-size: 16px;border:0;outline: none;border-radius: 10px;height:40px;}
        div.btn-div>div{width: 100%;margin-top:8px;color:#666;font-size:14px;}
        div.btn-div>div>span{color:#0000ff;}

        div.remarks{position: fixed;bottom: 60px;width: 95%;text-align: center;color:#666;font-size: 14px;}
    </style>
    <script src="/static/js/jquery-3.3.1.min.js"></script>
</head>
<body>
    <div class="login">
        <div class="name" id="bor">
            <span><img src="/img/shouji@2x.png"/></span>
            <input type="text" class="username" required placeholder="请输入手机号码" value=""/>
        </div>
        <div class="name" id="verify">
            <span><img src="/img/yanzhengma@2x.png"/></span>
            <input type="text" class="password" placeholder="请输入验证码" value=""/>
            <span class="ver" onclick="ver(this);">获取验证码</span>
        </div>
        <div class="tips-text"><span>请确认您填写的手机号码是正确的，可以接收到验证码。</span></div>

        <div class="btn-div">
            <input type="button" class="btn-button" value="登陆"/>
            <div>
                <label><input type="radio" flag="1" id="radio" name="radio"/>我同意</label><span class="user-agreement">《用户协议及隐私政策》</span>
            </div>
        </div>
        <div class="remarks">
            <span>温馨提示：未注册的用户，初次登录时将完成注册</span>
        </div>
    </div>

</body>
<script src="/static/js/com.js"></script>
<script>


    var timer = null,
            idx = 60;
    function code_ver(){
        idx--;
        $('span.ver').text('重新获取('+idx+')');
        $('span.ver').attr('onclick','');
        if(idx <= 0){
            $('span.ver').attr('onclick','ver(this);');
            $('span.ver').text('获取验证码');
            timer = clearInterval(timer);
            idx = 59;
        }
    }

    $('input#radio').click(function(){
        var flag = $(this).attr('flag');
        if(flag == 1){
            $(this).attr('flag',2);
        }else {
            $(this).attr('flag',1);
        }


    });

    $('input.btn-button').click(function(){

        var phone = $('input.username').val();
        var code = $('input.password').val();
        var flag = $('input#radio').attr('flag');

        if(phone && code && (flag == 2)){
            $('div#bor').css('border','0');
            $('div#verify').css('border','0');
            $('div.btn-div>div>label').css('border','0');

            $.ajax({
                url:"<?php echo url('login/logins'); ?>",
                data:{username:phone,code:code},
                type:'post',
                success:function(org){
                    org = JSON.parse(org);
                    if(org.status == 200){
                        localStorage.setItem('uid_',org.uid);
                        window.location.href = "<?php echo url('index/index'); ?>?u="+org.uid;
                    }else if(org.status == 202){
                        window.location.href = "<?php echo url('login/guide'); ?>?p="+phone;
                    }else{
                        msg('验证码错误');
                    }
                }
            });
        }else{
            /*$('div#bor').css('border','1px solid red');
            $('div#verify').css('border','1px solid red');
            $('div.btn-div>div>label').css('border','1px solid red');*/
        }


    });

    function ver(that){
        $('div.tips-text').css('display','block');
        var phone = $('input.username').val();

        var myreg = /^(((13[0-9]{1})|(14[0-9]{1})|(16[0-9]{1})|(17[0-9]{1})|(19[0-9]{1})|(18[0-9]{1})|(15[0-9]{1}))+\d{8})$/;
        //15198050252
        if(myreg.test(phone)){

            $('div#bor').css('border','0');
            timer = setInterval(code_ver,1000);
            /*$.ajax({
                url:"<?php echo url('login/send'); ?>",
                data:{username:phone},
                type:'post',
                success:function(org){
                    console.log(org);
                }
            });*/
        }else{
            $('div#bor').css('border','1px solid red');
        }

    }

    var u = localStorage.getItem('uid_');
    if(u){
        window.location.href = "<?php echo url('index/index'); ?>?u="+u;
    }


</script>
</html>
