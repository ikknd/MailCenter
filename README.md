MailCenter  
==
1. Initialization  
--

In order to use MailCenter you will need to call Mailing class.
```php
$mailing = new \MailCenter\Mailing($config, $type, $options, $emails);  
$mailing->run();
```
-------
$config is a stdClass.  

Example $config:
```php
$config = new stdClass();  
$config->host = ‘localhost’;  
$config->port = ‘3306’;  
$config->dbname = ‘test’;  
$config->username = ‘root’;  
$config->password = ‘root’;  
$config->path = ‘/../mailcenter’;      /*Path to Mailings described in section 2*/
```

------
$type – is a name of your mailing, it will be used to find Mailing files described in section 2

Example $type:
```php
$type = ‘appsale’;
```

------
$options - is an optional array of options that will be made available to DataProvider

Example $options:
```php
$options = array('appId'=>1);
```

------
$emails – is an optional array of emails that mailing will be sent to. If $emails is set to null, UserProvider will look for emails in database, using $type attribute to find users signed up for this mailing.

Example $emails:
```php
$emails = array(
    				0=>array(
    					'email'=>'test@test.com',
    					'username'=>'test'
    				)
    			)
OR
$emails = null;
```

2 Mailings  
--

If MailCenter folder is core of the project itself, then Mailings are specific implementations.
Mailings can be located anywhere in your project and are separate from MailCenter core, but path to Mailings folder needs to be specified in $config->path, when initializing MailCenter.  

Mailings follow this file\naming structure:
```php
  {mailingFolder}  
    - Data  
      -- {MailingName}Data.php  
    - Mailing  
      -- {MailingName}Mailing.php  
    - Template  
      -- {MailingName}Template.php  
```

So if you specified $type = 'appsale', like described in section 1, MailCenter would look for AppsaleData.php, AppsaleMailing.php and AppsaleTemplate.php

---------
{MailingName}Data.php

```php
use MailCenter\Data\DataInterface;

class AppsaleData implements DataInterface
{
    /**
     * @param $db \PDO
     * @param array $options
     * @return array
     */
    public static function getData($db, $options)
    {
        $data = ... \*Code to fetch data using $db - PDO object*\

        return $data;
    }
}
```

---------
{MailingName}Mailing.php

```php
use MailCenter\Mailing\MailingAbstract;
use MailCenter\Template\TemplateProvider;
use MailCenter\Sender\SenderProvider;

class AppsaleMailing extends MailingAbstract
{
    public function getConfig()
    {
        $config = new \stdClass();
        $config->subject = 'Appsale Daily Newsletter';
        $config->storage = TemplateProvider::STORAGE_TYPE_FILE;
        $config->engine = TemplateProvider::ENGINE_TYPE_PHP;
        $config->sender = SenderProvider::TYPE_MANDRILL;

        return $config;
    }
}
```  
Available Senders are - SenderProvider::TYPE_MANDRILL and SenderProvider::TYPE_SENDMAIL

---------
{MailingName}Template.php

An html email template.
