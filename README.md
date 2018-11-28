# *AmaderCAT* - A Data Collection System for Matchine Translation System

The application *AmaderCAT* is the abbreviation of **Amader Computer Assisted Translation**. This application is developed for the purpose of building parallel corpus for **Machine Translation** system. The application contains a Translation Memory and a Glossary suggestions implementation that used for helping translators by providing [TM](https://en.wikipedia.org/wiki/Translation_memory) and [glossary](https://en.wikipedia.org/wiki/Glossary) suggestions. The application is collaborative and highly configurable for the translation task. It has the mechanism for crowd translation. You can use it as single user or a group/team. In future, we will add **Machine Translation System** in our application using Neural Network technologies.

**The system is developed for supporting any language, however, we only evaluated for developing Bangla-English parallel corpus.**

The architecture and the user guidelines information is described in our papers:
 - [A Collaborative Platform to Collect Data for Developing Machine Translation System]() (*link will updated soon*)

## Setup

In order to use the application, there are following pre-requisites:
 - PHP 5.5 or higher.
 - MySQL 5.7
 - An apache 2.2/2.2+ or nginX 1.1/1.1+
Download the application or run the following command:
```
https://github.com/AridHasan/Data-Collection-System-for-Machine-Translation.git
```
Now, next tasks will be database configuration. Create a database with the name of *amader* or change the database configuration in *application/config/database.php* and *application/models/Auth.php* in *User* class. The table structures are in *database.sql* file in the root directory. Run every table structure on your *mysql* command prompt.
To create database please run the following command:
```
CREATE DATABASE `amader`
```
#### Configuring E-mail sending option
Before registration, e-mail configuration is mandatory. To configure e-mail option please modify the *send_mail* function in the *application/models/Auth.php* file.
The configuration should be like:
```
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

### Run the Application
Now, start *apache or nginX* server and *mysql on 3306* port.
Copy the following url in your browser:
```
https://localhost/your_application_directory/
or
https://localhost:configured_port_for_server/your_application_directory/
```
User guidelines are already provided in our [paper]() or you can see the [video tutorial](https://youtu.be/we266Q51P_Y).

## Our Corpus
You will find the parallel corpus that we build using our developed application *AmaderCAT* in the *data* folder of root directory. For Bengali and English parallel corpus the files name are *bengali.txt* and *english.txt*, respectively.

## Citation
If you find the application useful for developing parallel corpus, please cite the our paper [A Collaborative Platform to Collect Data for Developing Machine Translation System]() *link will updated soon*
```
BibTex will be updated soon. 
```
