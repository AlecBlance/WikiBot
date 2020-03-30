# WikiBot

Wikibot is a Facebook Messenger Chatbot that searches and gathers information from [Wikipedia](https://en.wikipedia.org/). It uses the Wikipedia [API](https://www.mediawiki.org/wiki/API:Main_page) for faster information gathering.

It aims to provide Wikipedia access to free data and mobile users.

## Downloading
Simply clone the repository

    git clone https://github.com/AlecBlance/WikiBot.git

Or download it as a zip file in the upper right corner.


## Configuration
The configuration includes two parts, the Facebook configuration and the WikiBot configuration. In order for the WikiBot to run it needs to have the following:
* Generated page token
* App secret key
* Developer's verification
```php
$config = [
    'facebook' => [
        'token' => '<FB PAGE TOKEN>',
        'app_secret' => '<APP SECRET KEY>',
        'verification'=>'<VERIFICATION>',
    ],
    'botman' => [
        'conversation_cache_time' => 0
    ]
];
```
These can be gathered in Facebook Developers' App Dashboard.
### Facebook Process
Getting Page Token:
 1. Create your Facebook Page
 2. Go to [Facebook Developers](https://developers.facebook.com/)
 3. Create your App
 4. Set up **Messenger** as your product
 5. Under "Access Token", add your page and generate a token (Take note of the token)

Getting App Secret Key

 1. Go to Settings > Basic
 2. In the **App Secret**, click Show (Take note of key)

### WikiBot Process

 1. Go to the cloned repository 
 2. Edit the WikiBot.php and find the following code:
 ```php
$config = [
    'facebook' => [
        'token' => '<FB PAGE TOKEN>',
        'app_secret' => '<APP SECRET KEY>',
        'verification'=>'<VERIFICATION>',
    ],
    'botman' => [
        'conversation_cache_time' => 0
    ]
];
```
 3. Insert your Page token, App secret key and desired verification (e.g. wikibot)
 4. Save  :)

## Installation
To run the WikiBot, a server must host the cloned repository. You may host it from your local XAMPP and deploy it online via ngrok or you may choose 000webhost instead.

> Facebook requires https domain

For your Facebook page to run the WikiBot:

 1. Go to your app dashboard
 2. Then go to Messenger > Settings
 3. Under Webhooks, add your **Callback URL**
     **Callback URL**: https://your-server.com/WikiBot.php
     **Verify Token**:  Your verification set in config (e.g. wikibot)
 4. Click Verify and Save
 5. Still, under Webhooks, click the edit button (same row with the page)
 6. Add these as subscriptions: *messages*, *messaging_postbacks*
 

To add a **Get Started** button in your WikiBot, run this curl command along with your "Page Access Token"

```
curl -X POST -H "Content-Type: application/json" -d '{
  "get_started": {"payload": "menu"}
}' "https://graph.facebook.com/v2.6/me/messenger_profile?access_token=<PAGE_ACCESS_TOKEN>"
```
## Running
This is the final process. 

 1. Go to your Messenger
 2. Search for your Page
 3. Click it and you will see the **Get Started** Button 
 4. Proceed and Enjoy \^_^

## Built with
[BotMan](https://botman.io/) - PHP Framework for ChatBot Development
[PHP](https://www.php.net/) - Programming Language
[Composer](https://getcomposer.org/) - # Dependency Manager for PHP

## Author

 - Alec Blance 
## License
This project is licensed under the MIT License - see the [LICENSE.md](https://gist.github.com/PurpleBooth/LICENSE.md) file for details
## Acknowledgements
 - This project is made possible by BotMan PHP Framework


