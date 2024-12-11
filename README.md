# SmsAssistent/Sender
[![Latest Stable Version](https://poser.pugx.org/sms-assistent/laravel-sender/v/stable)](https://packagist.org/packages/sms-assistent/laravel-sender)
[![Total Downloads](https://poser.pugx.org/sms-assistent/laravel-sender/downloads)](https://packagist.org/packages/sms-assistent/laravel-sender)
[![PHP Version Require](https://poser.pugx.org/sms-assistent/laravel-sender/require/php)](https://packagist.org/packages/sms-assistent/laravel-sender)
[![License](https://poser.pugx.org/sms-assistent/laravel-sender/license)](https://packagist.org/packages/sms-assistent/laravel-sender)

Sender - Laravel Provider for SMS-assistent.by


## Installation

``` bash
$ composer require sms-assistent/laravel-sender
```

Add to config/app.php in section ```aliases```:

``` php
'Sender' => SmsAssistent\Sender\Sender::class,
```

Publish package files by running 
```
php artisan vendor:publish --provider="SmsAssistent\Sender\SenderServiceProvider"
```
## Config

To configure connection with SMS-assistent add these options to your ```.env```:

```
SMS_ASSISTENT_USERAGENT={useragent, optional}
SMS_ASSISTENT_USERNAME={SMS-assistent login}
SMS_ASSISTENT_PASSWORD={API password}
SMS_ASSISTENT_SENDER_NAME={registered SMS sender name}
```
## Usage

Now, if you have configured ```Queues```, you can create a ```Job``` like this below in ```/App/Http/Jobs```
```
<?php

namespace App\Jobs;

use SmsAssistent\Sender\Sender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSMS implements ShouldQueue
{
    use Queueable;

    protected $to;
    protected $text;

    /**
     * @param $to
     * @param $text
     */
    public function __construct($to, $text)
    {
        $this->to   = $to;
        $this->text = $text;
    }

    public function handle(Sender $sender)
    {
        $sender->sendOne($this->to, $this->text);
    }
}
```

And after dispatch a new Job anywhere in your app like this
```
<?php

use App\Jobs\SendSMS;

class SampleController extends Controller
{
    public function test()
    {
        // Example 1
        $this->dispatch((new SendSms('+375295363600', 'Hello world!')));

        // Example 2
        dispatch(new SendSms('+375295363600', 'Hello world!'));

        // Example 3
        SendSms::dispatch('+375295363600', 'Hello world!');
    
    }
}
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
