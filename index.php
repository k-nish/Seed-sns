<?php
session_start();

require('dbconnect.php');
// var_dump($_SESSION);
//ログイン確認、tweetを受け取る
if (isset($_SESSION['member_id']) && $_SESSION['time'] + 3600 > time()) {
    $_SESSION['time'] = time();
    $sql = sprintf('SELECT * FROM `members` WHERE `member_id` =%d',
       mysqli_real_escape_string($db,$_SESSION['member_id']));
    $record = mysqli_query($db,$sql) or die(mysqli_error($db));
    $member = mysqli_fetch_assoc($record);
}
else{
    header('Location:login.php');
    exit();
}


//dbに投稿
if (!empty($_POST['tweet'])) {
  if($_POST['tweet'] != ''){
    $sql = sprintf('INSERT INTO `tweets` SET `tweet`="%s",`member_id`="%d",`reply_tweet_id`="%d",`created` = now()',
          mysqli_real_escape_string($db,$_POST['tweet']),
          mysqli_real_escape_string($db,$member['member_id']),
          mysqli_real_escape_string($db,$_POST['reply_tweet_id']));
    $record = mysqli_query($db,$sql) or die(mysqli_error($db));
    header('Location: index.php');
    exit();
  }
}

if (isset($_GET['find'])&&!empty($_GET['find'])) {
    //ページング処理
    $pg='';
    if(isset($_REQUEST['pg'])){
        $pg = $_REQUEST['pg'];
    }
    if ($pg == '') {
        $pg == 1;
    }
    $pg = max($pg,1);

    //最終ページ取得
    // $sq = 'SELECT COUNT(*) AS cnt FROM `tweets` WHERE `tweet` LIKE "%'.$_POST['find'].'%"';
    $sq = sprintf('SELECT COUNT(*) AS cnt FROM `tweets` WHERE `tweet` LIKE "%%%s%%"',
           mysqli_real_escape_string($db,$_GET['find']));
    $recordset = mysqli_query($db,$sq) or die(mysqli_error($db));
    $tables = mysqli_fetch_assoc($recordset);
    $maxpg = ceil($tables['cnt'] / 5);
    $pg = min($pg,$maxpg);
    var_dump($maxpg);
    var_dump($pg);
    var_dump($tables['cnt']);
    //以上で$pageの定義完了

    $starts = ($pg-1)*5;
    $starts = max(0,$starts);
    var_dump($maxpg);

    //あいまい検索実行
    $sql = 'SELECT m.nick_name,m.picture_path,t.* FROM `tweets` t,`members` m WHERE t.member_id = m.member_id
                    AND `tweet` LIKE "%'.$_GET['find'].'%" ORDER BY t.created DESC LIMIT '.$starts.',5';
          // mysqli_real_escape_string($db,$_POST['find']));
    $stmt = mysqli_query($db,$sql) or die(mysqli_error($db));
    //検索結果のページング


}else{
    //現在のページを選択
    $page='';
    if(isset($_REQUEST['page'])){
        $page = $_REQUEST['page'];
    }
    if ($page == '') {
        $page == 1;
    }
    $page = max($page,1);

    //最終ページ取得
    $sql = 'SELECT COUNT(*) AS cnt FROM `tweets`';
    $recordSet = mysqli_query($db,$sql);
    $table = mysqli_fetch_assoc($recordSet);
    $maxpage = ceil($table['cnt'] / 5);
    $page = min($page,$maxpage);
    //以上で$pageの定義完了

    $start = ($page-1)*5;
    $start = max(0,$start);
    //これまでの投稿表示
    $sql = sprintf('SELECT m.nick_name,m.picture_path,t.* FROM `tweets` t,`members` m WHERE t.member_id = m.member_id ORDER BY t.created DESC LIMIT %d,5',$start);
    $records = mysqli_query($db,$sql) or die(mysql_error());
}
//返信元のtweetを表示
if (isset($_REQUEST['res'])) {
    $sql = sprintf('SELECT m.`nick_name`,m.`picture_path`,t.* FROM `tweets`t,`members`m WHERE m.`member_id`=t.`member_id` AND t.`tweet_id`=%d',
      mysqli_real_escape_string($db,$_REQUEST['res']));
    $record = mysqli_query($db,$sql) or die(mysql_error());
    $resp = mysqli_fetch_assoc($record);
    $message = '>@'.$resp['nick_name'].' '.$resp['tweet'];
}

//htmlspecialchars()のfunctionを設置
function h($value){
    return htmlspecialchars($value,ENT_QUOTES,'UTF-8');
}
//URL表示用のfunctionを設置
function makeLink($value){
  // return mb_ereg_replace("(https?)(://[[:alnum:]¥+¥$¥;¥?¥.%,!#~*/:@&=_-]+)",'<a href="¥1¥2">¥1¥2</a>',$value);
  return mb_ereg_replace('(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+)','<a href="\1" target="_blank">\1</a>',$value);
}
 ?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SeedSNS</title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/form.css" rel="stylesheet">
    <link href="assets/css/timeline.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.php"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.php">ログアウト</a></li>
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-4 content-margin-top">
        <legend>ようこそ<?php echo h($member['nick_name']);?>さん！</legend>
        <form method="post" action="" class="form-horizontal" role="form">
            <!-- つぶやき -->
            <div class="form-group">
              <label class="col-sm-4 control-label">つぶやき</label>
              <div class="col-sm-8">
                <?php if (isset($_REQUEST['res'])) { ?>
                <textarea name="tweet" cols="50" rows="5" class="form-control"><?php echo h($message);?></textarea>
                <?php }else{ ?>
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"></textarea>
                <?php } ?>
                  <input type='hidden' name="reply_tweet_id" value="<?php echo h($_REQUEST['res']); ?>"/>
              </div>
            </div>
          <ul class="paging">
            <input type="submit" class="btn btn-info" value="つぶやく">
                <?php if (isset($page)) { ?>
                <?php if ($page > 1){ ?>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <li><a href="index.php?page=<?php print($page-1); ?>" class="btn btn-default">前</a></li>
                <?php }else{ ?>
                <li>前のページへ</li>
                <?php } ?>
                <?php if ($page < $maxpage) { ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <li><a href="index.php?page=<?php echo ($page+1); ?>" class="btn btn-default">次</a></li>
                <?php }else{ ?>
                <li>次のページへ</li>
                <?php }} ?>
          </ul>
        </form>

        <legend>検索ボックス！</legend>
        <form method="get" action="" class="form-horizontal" role="form">
            <!-- 検索 -->
            <div class="form-group">
              <label class="col-sm-4 control-label">検索!</label>
              <div class="col-sm-8">
                <input name="find" cols="50" rows="5" class="form-control" placeholder="あいまい検索">
                  <!-- <input type='hidden' name="reply_tweet_id" value="<?php echo htmlspecialchars($_REQUEST['res'], ENT_QUOTES,'UTF-8'); ?>"/> -->
              </div>
            </div>
        <ul class="paging">
            <input type="submit" class="btn btn-info" value="検索する">
                <?php if (isset($pg)) { ?>
                <?php if ($pg > 1){ ?>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <li><a href="index.php?pg=<?php print ($pg-1); ?>&find=<?php echo h($_GET['find']); ?>" class="btn btn-default">前</a></li>
                <?php }else{ ?>
                <li>前のページへ</li>
                <?php } ?>
                <?php if ($pg < $maxpg) { ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <li><a href="index.php?pg=<?php echo ($pg+1); ?>&find=<?php echo h($_GET['find']); ?>" class="btn btn-default">次</a></li>
                <?php }else{ ?>
                <li>次のページへ</li>
                <?php }} ?>
          </ul>
        </form>
      </div>



      <?php if (isset($_GET['find'])&&!empty($_GET['find'])) {
                while($twt = mysqli_fetch_assoc($stmt)): ?>
      <div class="col-md-8 content-margin-top">
        <div class="msg">
          <img src="member_picture/<?php echo h($twt['picture_path']);?>" width="48" height="48">
          <p>
            <strong><Font size="4"><?php echo makeLink(h($twt['tweet'])); ?></strong> <span class="name"> (<?php echo h($twt['nick_name']); ?>)</span>
            [<a href="index.php?res=<?php echo h($tweet['tweet_id']); ?>">Re</a>]
          </p>
          <p class="day">
            <a href="view.php?id=<?php echo h($twt['tweet_id']); ?>">
              <?php echo h($twt['created']); ?>
            </a>
            <?php if ($twt['reply_tweet_id']>0) {?>
                <a href="view.php?id=<?php echo h($twt['reply_tweet_id']); ?>">返信元のつぶやき</a>
            <?php } ?>
            [<a href="edit.php?id=<?php echo h($twt['tweet_id']); ?>" style="color: #00994C;">編集</a>]
            <?php if($_SESSION['member_id']==$twt['member_id']){ ?>
            [<a href="delete.php?id=<?php echo h($twt['tweet_id']); ?>" style="color: #F33;">削除</a>]
            <?php } ?>
          </p>
        </div>
      </div>
      <?php endwhile; }else{ while($tweet = mysqli_fetch_assoc($records)): ?>
      <div class="col-md-8 content-margin-top">
        <div class="msg">
          <img src="member_picture/<?php echo h($tweet['picture_path']);?>" width="48" height="48">
          <p>
            <strong><Font size="4"><?php echo makeLink(h($tweet['tweet'])); ?></strong> <span class="name"> (<?php echo h($tweet['nick_name']); ?>)</span>
            [<a href="index.php?res=<?php echo h($tweet['tweet_id'],ENT_QUOTES,'UTF-8'); ?>">Re</a>]
          </p>
          <p class="day">
            <a href="view.php?id=<?php echo h($tweet['tweet_id']); ?>">
              <?php echo h($tweet['created']); ?>
            </a>
            <?php if ($tweet['reply_tweet_id']>0) {?>
                <a href="view.php?id=<?php echo h($tweet['reply_tweet_id']); ?>">返信元のつぶやき</a>
            <?php } ?>
            <?php if($tweet['member_id'] == $_SESSION['member_id']){ ?>
            [<a href="edit.php?id=<?php echo h($tweet['tweet_id']); ?>" style="color: #00994C;">編集</a>]
            <?php if($_SESSION['member_id']==$tweet['member_id']){ ?>
            [<a href="delete.php?id=<?php echo h($tweet['tweet_id']); ?>" style="color: #F33;">削除</a>]
            <?php }} ?>
          </p>
        </div>
      </div>
      <?php endwhile; }?>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>