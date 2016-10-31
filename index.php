
<?php
	
require 'phpplot/phplot.php';

	// Определяем параметры клиента
	@ $db = mysqli_connect("localhost", "skyeng", "skYe2ng201fd@", "skyeng");
	mysqli_set_charset($db, 'utf8');
			
	if (mysqli_connect_errno()) exit;
	
	$query = "
		CREATE TABLE IF NOT EXISTS client_status ( 
		id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
		name VARCHAR(20) NOT NULL,
		surname VARCHAR(20) DEFAULT '' NOT NULL,
		status ENUM('new', 'registered', 'cancelled', 'not available') DEFAULT 'new' NOT NULL,
		datetime DATETIME NOT NULL
		) ";

	$result = mysqli_query($db, $query);
	
	$query = "SELECT count(1) as counter from client_status";
	$result=mysqli_query($db, $query);
	$row = mysqli_fetch_assoc($result);		
	
	if ($row['counter'] <40){	
		for( $i= 0 ; $i <= 40 ; $i++ )
		{
			$names = array('Август', 'Августин', 'Аврор', 'Агап', 'Аксён', 'Алевтин', 'Альберт', 'Анвар', 'Антип', 'Аристарх', 'Артамон', 'Архип', 'Аскольд', 'Афанасий', 'Афиноген', 'Борислав', 'Валерьян', 'Вацлав', 'Велимир', 'Велор', 'Вениамин', 'Викентий', 'Владлен', 'Власий', 'Володар', 'Вольдемар', 'Гаспар', 'Дементий', 'Добрыня', 'Дорофей', 'Евграф', 'Евдоким', 'Евлампий', 'Евлогий', 'Евсей', 'Евстафий', 'Елисей', 'Емельян', 'Епифан', 'Еремей', 'Ермак', 'Ермил', 'Ермолай', 'Ерофей', 'Ефим', 'Ефрем', 'Захар', 'Зиновий', 'Зорий', 'Зот', 'Изяслав', 'Ипполит', 'Кай', 'Каспар', 'Ким', 'Кир', 'Клавдий', 'Клементий', 'Клим', 'Кондратий', 'Кондрат', 'Краснослав', 'Лаврентий', 'Лазарь', 'Ларион', 'Лаврентий', 'Лазарь', 'Ларион', 'Лука', 'Лукьян', 'Макар', 'Марсель', 'Мануил', 'Мартын', 'Мартин', 'Матвей', 'Мефодий', 'Мечеслав', 'Милад', 'Милен', 'Милослав', 'Мир', 'Мирон', 'Мирослав', 'Наум', 'Неонил', 'Нестор', 'Никанор', 'Никодим', 'Ольгерд', 'Онисим', 'Осип', 'Оскар', 'Остап', 'Пантелеймон', 'Панфил', 'Парамон', 'Пахом', 'Пересвет', 'Платон', 'Потап', 'Прозор', 'Прокофий', 'Прохор', 'Радислав', 'Радомир', 'Родион', 'Ролан', 'Ростислав', 'Савелий', 'Самсон', 'Свет', 'Светлан', 'Светозар', 'Северьян', 'Семён', 'Серафим', 'Тарас', 'Тельнан', 'Терентий', 'Февралий', 'Фома', 'Фрол');
			shuffle($names);
			$name = $names[0];	
			$surname=$names[1];
			
			$status = array('new','registered','cancelled','not available');
			shuffle ($status);
			$current=$status[0];
			
			$date=date('Y-m-d H:i:s', strtotime( '-'.mt_rand(0,30).' days'));
			
			$query = "INSERT INTO `client_status`(`name`, `surname`, `status`, `datetime`) VALUES
									('".$name."','".$surname."','".$current."','".$date."')";
				
			$result = mysqli_query($db, $query)or die(mysql_error());
			if (!$result) exit;
		}
	}
	
	$query = "SELECT min(datetime) min_date, max(datetime) max_date from client_status";
	$result=mysqli_query($db, $query);
	$row = mysqli_fetch_assoc($result);	

	$min_date=date('Y-m-d',strtotime($row['min_date']));
	$max_date=date('Y-m-d',strtotime($row['max_date']));
	
	
	$period=$_POST['period'];
	if(empty($period)) $period=1;
	
	$query = "
		CREATE TABLE IF NOT EXISTS periods ( 
		id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
		date_start DATETIME NOT NULL,
		date_end DATETIME NOT NULL
		) ";
	
	$result = mysqli_query($db, $query) or die(mysql_error());
	$query = "
		truncate table periods";
	$result = mysqli_query($db, $query) or die(mysql_error());
	
	$cur_date=$min_date;
	
	while ($cur_date < $max_date) {
	
		if($cur_date==$min_date){
		$start_date=date('Y-m-d H:i:s', strtotime($cur_date));}
		else {
			$start_date=date('Y-m-d H:i:s', strtotime($cur_date.'+1 second'));
		}
		$cur_date = date('Y-m-d H:i:s', strtotime($cur_date.'+'.$period.' days'));

		$query = "INSERT INTO `periods`(`date_start`, `date_end`) VALUES
									('".$start_date."','".$cur_date."')";
				
			$result = mysqli_query($db, $query)or die(mysql_error());
			if (!$result) exit;
		
		
	}
	
	$query = "
	SELECT p.id, count(c.id) as total, sum(case when c.status='registered' then 1 else 0 end) as registered FROM `client_status` c 
	inner join `periods` p on c.datetime between p.date_start and date_end
	WHERE 1
	group by p.id";
	
	$result = mysqli_query($db, $query) or die(mysql_error());

	$arr=array();
	$element=array();
	while($row = mysqli_fetch_assoc($result)) { 
	$element=array('',$row['id'],$row['registered']/$row['total']);
	$arr[]=$element;
	
	}

	
$plot = new PHPlot();
$data = $arr;

$plot = new PHPlot(600, 400);

$plot->SetPlotType('lines');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);
$plot->SetIsInline(true);
$plot->SetFailureImage(False); // No error images
$plot->SetPrintImage(False); // No automatic output
$plot->SetTitle('Conversion rate by periods');
$plot->DrawGraph();

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
     "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>График конверсии</title>
</head>
<body>
<h1>График конверсии</h1>
<img src="<?php echo $plot->EncodeImage();?>" alt="Уровень конверсии">
<form method="post" action="">
<input type='text' name='period'>
<input type='submit' value='Задать период (в днях)'>
</form>
	
</body>
</html>


	
	