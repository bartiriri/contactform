<?php 
    // początek tworzenia klasy odpowiadającej za łączenie z bazą danych 
    class Database
    {
        private static $dbName = 'nowabaza';
        private static $dbHost = '127.0.0.1';
        private static $dbUsername = 'root';
        private static $dbUserPassword = '';

        public static $cont = null;

        public function __construct()
        {
            die('Funkcja init nie jest dozwolona');
        }

        public static function connect()
        {
            if(null == self::$cont)
            {
                try
                {
                    self::$cont = new PDO("mysql:host=".self::$dbHost.";"."dbname=".self::$dbName,self::$dbUsername,self::$dbUserPassword);
                }
                catch(PDOException $e)
                {
                    die($e->getMessage());
                }

            }
            return self::$cont;
        }
        public static function disconnect()
        {
            self::$cont = null;
        }
    }
    // koniec tworzenia klasy odpowiedzialnej za łączenie z bazą danych

?>
<?php
    // kod odpowiedzialny za walidowanie oraz wysyłanie danych do bazy danych - początek
    if(!empty($_POST))
    {
        $firstNameError = null;
        $lastNameError = null;
        $emailError = null;
        $phoneError = null;
        $messagesError = null;

        
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $messages = $_POST['messages'];

        $walidacja = true;

        if(empty($firstName)){
            $firstNameError = "nie wpisałeś imienia!!!";
            $walidacja = false;
            
        }
        else if(strlen($firstName)>255){
            $firstNameError = "za długie imię, maksymalnie 255 znaków!!!";
            $walidacja = false;
        }
        if(empty($lastName)){
            $firstNameError = "nie wpisałeś nazwiska!!!";
            $walidacja = false;
            
        }
        
        
        if(empty($email)){
            $emailError = "nie wpisałeś emaila!!!";
            $walidacja = false;
            
        }
        else{
            if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
                $emailError = "wprowadź poprawny adres email!!!";
                $walidacja = false;
                
            }
        }
        if(empty($phone)){
            $phoneError = "nie wpisałeś telefonu!!!";
            $walidacja = false;
            
        }
        if(empty($messages)){
            $messagesError = "nie wpisałeś treści wiadomości!!!";
            $walidacja = false;
            
        }
    
    if($walidacja ){
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $sql = "INSERT INTO contactform (firstName,lastName,email,phone,messages) values(?,?,?,?,?)";
        $q = $pdo->prepare($sql);
        $q ->execute(array($firstName,$lastName,$email,$phone,$messages));
        $firstName = null;
        $lastName = null;
        $email = null;
        $phone = null;
        $messages = null;
        $successMessage = "Udało się przesłać wiadomość!!!";
        Database::disconnect();
    }
}
    // kod odpowiedzialny za walidowanie oraz wysyłanie danych do bazy danych - koniec
            
    // funkcja odpowiedzialna za pobieranie do pliku csv wszystkich danych wysłanych przez formularz   
     function pobierzCSV(){
        $pdo3 = Database::connect();
        $sql3 = 'SELECT * FROM contactform ';
        $dane1 = $pdo3->query($sql3);
         $dane = $dane1->fetchAll(PDO::FETCH_ASSOC);
         $data = array();
         $data[0]['id'] = 'id';
         $data[0]['firstName'] = 'imię';
         $data[0]['lastName'] = 'nazwisko';
         $data[0]['email'] = 'email';
         $data[0]['phone'] = 'telefon';
         $data[0]['messages'] = 'wiadomość';

         $datas = array_merge($data,$dane);

         foreach($datas as $key => $data){
             foreach($data as $key_s => $single_data){
             $datas[$key][$key_s] = iconv("UTF-8","cp1250", $single_data);
             }
             }
             $filename = 'dane.csv';

             $FileHandle = fopen($filename, 'w+') or die("can't open file");
             
             foreach($datas as $key => $data) {
             fputcsv($FileHandle, $data, ';', '"');
             }
             
             fclose($FileHandle);
             $csv= "Udało się zapisać dane do pliku";
}

?>
<!doctype html>
<html>
    <head>
    <style>
    .color-red{color:red;}
    .color-green{color:green;}
    </style>
    </head>
    <body>
    <?php if (!empty($successMessage)): ?>
        <h2 class="color-green"><?php echo $successMessage;?></h2><br>
    <?php endif; ?>
    <form action="index.php" method="post">
    <label>Imię: </label><br>
    <input name="firstName" type="text" value="<?php echo !empty($firstName)?$firstName:'';?>"/><br>
    <?php if (!empty($firstNameError)): ?>
        <small class="color-red"><?php echo $firstNameError;?></small><br>
    <?php endif; ?>
    <label>Nazwisko: </label><br>
    <input name="lastName" type="text" value="<?php echo !empty($lastName)?$lastName:'';?>"/><br>
    <?php if (!empty($lastNameError)): ?>
        <small class="color-red"><?php echo $lastNameError;?></small><br>
    <?php endif; ?>
    <label>Email: </label><br>
    <input name="email" type="email" value="<?php echo !empty($email)?$email:'';?>"/><br>
    <?php if (!empty($emailError)): ?>
        <small class="color-red"><?php echo $emailError;?></small><br>
    <?php endif; ?>
    <label>Telefon: </label><br>
    <input name="phone" type="text" value="<?php echo !empty($phone)?$phone:'';?>"/><br>
    <?php if (!empty($phoneError)): ?>
        <small class="color-red"><?php echo $phoneError;?></small><br>
    <?php endif; ?>
    <label>Treść wiadomości:</label><br>
    <textarea name="messages" type="textarea"  ><?php echo !empty($messages)?$messages:'';?></textarea><br>
    <?php if (!empty($messagesError)): ?>
        <small class="color-red"><?php echo $messagesError;?></small><br>
    <?php endif; ?>
    
    <button type="submit">Wyślij wiadomość</button>
    </form>


    <br><br>
    <button onclick="<?php pobierzCSV(); ?>"> Pobierz wszystkie odpowiedzi do pliku CSV</button>
    </body>
    
    
           
    
</html>