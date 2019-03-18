# *AmaderCAT:* A Data Collection System for Machine Translation System

## Objective & Description
The application *AmaderCAT* is the abbreviation of **Amader Computer Assisted Translation**. This application is developed for the purpose of building parallel corpus for **Machine Translation** system. The application contains a Translation Memory and a Glossary suggestions implementation which helps translators by providing [TM](https://en.wikipedia.org/wiki/Translation_memory) and [glossary](https://en.wikipedia.org/wiki/Glossary) suggestions. The application is collaborative and highly configurable for the translation task. It has the mechanism for crowd translation. You can use it as a single user or a group/team. In future, we will add **Machine Translation System** in our application using Neural Network technologies.

**This developed system supports any language, however, we only evaluated for developing Bangla-English parallel corpus.**

The information about architecture and user guidelines is described in our paper and thesis site:
 - [A Collaborative Platform to Collect Data for Developing Machine Translation System]() (*paper link will updated soon*)
 - Please visit my thesis site to know more, [Machine Translation System](https://sites.google.com/diu.edu.bd/mtbn2en)
 
**<p align="center">For better experiences, please visit the demo site:- <a href="https://translate.ejeex.com" title="AmaderCAT: A Machine Translation Tool for Bangla">AmaderCAT: A Machine Translation Tool for Bangla</a></p>**

## Setup
To configure this application, following necessary steps must need to be performed:
### Prerequisites
 - [PHP 5.5 or higher (*PHP 7.1 recommended*)](http://php.net/downloads.php).
 - [MySQL 5.7 or higher](https://dev.mysql.com/downloads/installer/)
 - Server: [Apache (version 2.2 or higher)](http://httpd.apache.org/download.cgi) or [NGINX (version 1.1 or higher)](https://nginx.org/en/download.html)

We used [CodeIgniter (v3.1.7)](https://www.codeigniter.com/) framework to develop this application.
To extends this corpus building application, please also see the [CodeIgniter Documentation](https://codeigniter.com/docs).

### Download
*Please download or run the following command to clone this repository:*
```repo link
https://github.com/AridHasan/Data-Collection-System-for-Machine-Translation.git
```
### Database Configuration
Create a database with the name of *amader* or change the database configuration in *application/config/database.php* and *application/models/Auth.php* in *User* class. The table structures are in *database.sql* file in the root directory. Run every table structure on your *MySQL* command prompt.

*Please run this following command to create database:*
```create database
CREATE DATABASE IF NOT EXISTS `amader`
```

### Configuring E-mail sending option
Before registration, e-mail configuration is mandatory. Please modify the *send_mail* function in the *application/models/Auth.php* file to configure the e-mail option.
The configuration should be like:
```email configure
$config = Array(
            'protocol' => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_port' => 465,
            'smtp_user' => 'your_gmail_account@gmail.com',
            'smtp_pass' => 'your_password',
            'mailtype'  => 'html',
            'charset'   => 'iso-8859-1'
        );
```

### Run this Application
Please start *Apache or NGINX* server and *MySQL on 3306 port* on your machine.
Then copy this following url in your browser:
```run url
http://localhost/your_application_directory/
or
http://localhost:configured_port_for_server/your_application_directory/

For example:
http://localhost:8080/Data-Collection-System-for-Machine-Translation/
```
Administration and user guidelines already provided in our [paper]() or you can see the [video tutorial](https://youtu.be/we266Q51P_Y).

## Our Developed Corpus
You will find our parallel **Bangla to English** corpus which developed by using this *AmaderCAT* application in the [data](https://github.com/AridHasan/Data-Collection-System-for-Machine-Translation/tree/master/data) folder of root directory. For Bengali and English parallel corpus the files name are *bengali.txt* and *english.txt*, respectively.
## Citation
If you find this application useful and use this system for developing your parallel corpus, please cite this paper, please cite this paper [A Collaborative Platform to Collect Data for Developing Machine Translation System]() (*paper link will be updated soon*)

*Md. Arid Hasan, Firoj Alam & Sheak Rashed Haider Noori, A Collaborative Platform to Collect Data for Machine Translation System, International Joint Conference on Computational Intelligence - IJCCI 2018.*

```bib
@inproceedings{hasan2018collaborative,
	title={A Collaborative Platform to Collect Data for Machine Translation System},
	author={Hasan, Md. Arid and Alam, Firoj and Noori, Sheak Rashed Haider},
	booktitle={Proceedings of International Joint Conference on Computational Intelligence - IJCCI 2018},
	pages={XX--XXX},
	year={2018},
	publisher={Springer}
}
```
