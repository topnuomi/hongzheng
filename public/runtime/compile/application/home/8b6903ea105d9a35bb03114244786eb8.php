<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
父级模板

    BODY
    <?php $b = 1;  echo ($b); ?>
    
        <volist name="b" id="vo">
            <if condition="$a == 1">
                {$a}
            <else condition="$a == 2" />
                {$b}
            </if>
        </volist>
    
    <?php echo (date('Y-m-d H:i:s', time())); ?>
    a.html
b.html
    <?php echo '你好'; ?>

</body>
</html>