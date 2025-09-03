#勤怠管理アプリ 

##環境構築  

###Docerビルド  
```  
git clone git@@github.com:sa-0117/attendance-management.git
docker-compose up -d --build  
``` 

###Laravel環境構築    
```  
docker-compose exec php bash  
composer install  
.env.example  
php artisan key:generate  
php artisan migrate  
php artisan db:seed 
```  

##使用技術  

* PHP 7.4.9
* Laravel 8.83.8 
* MySQL 8.0  

##ER図

![er](https://github.com/user-attachments/assets/138491c1-1bd0-4d85-9709-101de566b42e)

##テストアカウント

* 一般ユーザー
  name:一般ユーザー
  email：user@example.com
  password：password123

* 管理者ユーザー
  email：testadmin@example.com
  password：adminpassword

  ###PHPUnitを利用したテストについて
  ```
  docker-compose exec mysql bash
  mysql -u root -p
  create database demo_test;
  
  docker-compose exec php bash
  php artisan migrate:fresh --env=testing
  ./vendor/bin/phpunit
  ```
  ※mysqlのパスワードは「root」

  ####テスト内のテストアカウント
  * ステータスが勤務外のユーザー
    name: 勤務外ユーザー
    email：off@example.com
    password：password123

  * ステータスが出勤中のユーザー
    name: 出勤中ユーザー
    email：working@example.com
    password：password123


  * ステータスが休憩中のユーザー
    name: 休憩中ユーザー
    email：break@example.com
    password：password123
  
  * ステータスが退勤済のユーザー
    name: 退勤済みユーザー
    email：end@example.com
    password：password123
  

##URL  

* 開発環境：http://localhost/ 
* phpMyAdmin:http://localhost:8080
