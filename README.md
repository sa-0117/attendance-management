#勤怠管理アプリ 

##環境構築  

###Docerビルド  
```  
git clone git@github.com:sa-0117/flea-market-app.git
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

* PHP 8.4  
* Laravel 8.4  
* MySQL 8.0  
* mailtrap

##ER図




##URL  

* 開発環境：http://localhost/ 
* phpMyAdmin:http://localhost:8080