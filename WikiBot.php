<?php
/**
 * Wikipedia search in Messenger
 * PHP version 7.2.24
 *
 * Copyright 2020 Alec Blance
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentationfiles (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy,modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THEWARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @category Main
 * @package  Wikipedia
 * @author   Alec Blance <17378596+AlecBlance@users.noreply.github.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/AlecBlance
 */

namespace Wikipedia;

require 'vendor/autoload.php';
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Cache\SymfonyCache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Facebook\Extensions\ButtonTemplate;
use BotMan\Drivers\Facebook\Extensions\ElementButton;

/**
 * Wikipedia Information Scraping
 *
 * Uses the Conversation concept
 *
 * @category Class
 * @package  Wikipedia
 * @author   Alec Blance <17378596+AlecBlance@users.noreply.github.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/AlecBlance
 */
class Wikipedia extends Conversation
{
    protected $xml;
    protected $content;
    protected $i;
    protected $query;
    protected $array = array();

    /**
     * Provides Menu Button
     *
     * The menu button sends a postback action to Facebook and
     * Facebook will send the payload to your bot. Enabling
     * the user to go back to Menu
     *
     * @return NULL
     */
    public function done()
    {
        $this->say(
            ButtonTemplate::create(
                'We are done! 😇'
            )
            ->addButton(
                ElementButton::create(
                    'Menu'
                )
                ->type('postback')
                ->payload('menu')
            )
        );
    }
    /**
     * Searches for the Nearest Topic
     *
     * Uses the Wikipedia API to search for nearest topics.
     * Listing it in a decending order. This shows also a
     * stop button to stop the current search or process
     *
     * @return NULL
     */
    public function search()
    {
         $question = Question::create("What to search? ^_^")
            ->fallback('Search error')
            ->addButtons(
                [
                    Button::create('stop')->value('stop12stop')
                ]
            );
        $this->ask(
            $question,
            function (Answer $answer) {
                if ($answer->getValue() == 'stop12stop') {
                    $this->done();
                    return true;
                }
                $this->xml= file_get_contents(
                    "https://en.wikipedia.org/w/api.php?action=query&list=search&".
                    "srsearch=".str_replace(" ", "%20", $answer->getText())."&utf8=&"
                    ."format=json"
                );
                $this->xml=  json_decode($this->xml);
                $this->xml=  $this->xml->query;
                $this->content = "";
                $this->i =1;
                foreach ($this->xml->search as $id => $data) {
                    array_push($this->array, str_replace(" ", "%20", $data->title));
                    $this->content .= "[".$this->i++."] ".($data->title)."\n";
                }
                $this->say($this->content);
                $this->getIntro();
            }
        );
    }

    /**
     * Getting the Introduction of the selected topic
     *
     * Uses the API of wikipedia to show the intro in
     * each topics but only extracts the specific title
     * into.
     *
     * It also validates whether the response of the
     * user is a valid numerical value or a text
     *
     * @return NULL
     */
    public function getIntro()
    {
        $question = Question::create("Please select here ☝️")
            ->fallback('Unable to get information')
            ->addButtons(
                [
                Button::create('stop')->value('stop12stop')
                ]
            );
        $this->ask(
            $question,
            function (Answer $answer) {
                if ($answer->getValue() == 'stop12stop') {
                    $this->done();
                    return true;
                }
                if (!is_numeric($answer->getText())) {
                    $this->say("❗ It should be a number ❗");
                    foreach ($this->content as $value) {
                        $this->say($value);
                    }
                    $this->getIntro();
                    return true;
                }
                $this->query = $this->array[$answer->getText()-1];
                $this->xml= file_get_contents(
                    "https://en.wikipedia.org/w/api.php?format=json&action=query&".
                    "prop=extracts&titles=".$this->query."&explaintext=&exintro="
                );
                $this->xml = json_decode($this->xml);
                $this->xml = $this->xml->query;
                $this->content = "";
                foreach ($this->xml->pages as $id => $data) {
                    $this->content .= ($data->extract)."\n";
                }
                $this->content = str_split($this->content, 2000);
                foreach ($this->content as $value) {
                    $this->say($value);
                }
                $this->getParts();
            }
        );
    }

    /**
     * Extracts the parts of the wikipedia topic
     *
     * Uses the same api endpoint with getIntro function
     * but omits the exIntro parameter to show the full
     * text version of the Wikipedia topic.
     *
     * Uses the regex to extract the topics between the
     * equals sign
     *
     * @return NULL
     */
    public function getParts()
    {
        $question = Question::create(
            "There are parts in this wiki\n\nDo you wanna see it?"
        )
            ->fallback('Unable to get parts of this wiki')
            ->addButtons(
                [
                Button::create('Yes')->value('yes'),
                Button::create('No')->value('stop12stop')
                ]
            );

        $this->ask(
            $question,
            function (Answer $answer) {
                if ($answer->isInteractiveMessageReply()) {
                    $selectedValue = $answer->getValue();
                    if ($selectedValue == "stop12stop") {
                        $this->done();
                        return true;
                    }
                    $this->xml= file_get_contents(
                        "https://en.wikipedia.org/w/api.php?format=json&action=query"
                        ."&prop=extracts&titles=".$this->query."&explaintext="
                    );
                    $this->xml = json_decode($this->xml);
                    $this->xml = $this->xml->query;
                    $this->content = "";
                    $this->i=1;
                    foreach ($this->xml->pages as $id => $data) {
                        $this->xml = $data->extract;
                        preg_match_all('~(\=\s)(.*?)(\s\=)~', $this->xml, $matches);
                        foreach ($matches[2] as $value) {
                            $this->content .= "[".$this->i++."] ".$value."\n";
                        }
                    }
                    $this->content = str_split($this->content, 2000);
                    foreach ($this->content as $value) {
                        $this->say($value);
                    }
                    $this->getContent();
                }
            }
        );
    }
    /**
     * Gets the content of the selected part
     *
     * Uses the exact same api call in thegetParts function.
     * It separates the parts by using the \n\n\n delimiter.
     * It accesses the parts by using the array index
     *
     * @return type
     */
    public function getContent()
    {
        $question = Question::create("Please select here ☝️")
            ->fallback('Unable to get the part\'s content')
            ->addButtons(
                [
                Button::create('stop')->value('stop12stop')
                ]
            );
        $this->ask(
            $question,
            function (Answer $answer) {
                if ($answer->getValue() == 'stop12stop') {
                    $this->done();
                    return true;
                }
                if (!is_numeric($answer->getText())) {
                    $this->say("❗ It should be a number ❗");
                    foreach ($this->content as $value) {
                        $this->say($value);
                    }
                    $this->getContent();
                    return true;
                } else {
                    $this->xml= file_get_contents(
                        "https://en.wikipedia.org/w/api.php?format=json&action=query"
                        ."&prop=extracts&titles=".$this->query."&explaintext="
                    );
                    $this->xml = json_decode($this->xml);
                    $this->xml = $this->xml->query;
                    foreach ($this->xml->pages as $id => $data) {
                        $this->xml = $data->extract;
                        $this->xml = explode("\n\n\n", $this->xml);
                        $this->xml = $this->xml[$answer->getText()];
                        $this->xml = str_split($this->xml, 2000);
                        foreach ($this->xml as $value) {
                            $this->say($value);
                        }
                        // $this->say($this->xml);
                    }
                    $this->done();
                }
            }
        );
    }

    /**
     * Runs the Wikipedia Conversation
     *
     * @return NULL
     */
    public function run()
    {
        $this->search();
    }
}
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
DriverManager::loadDriver(\BotMan\Drivers\Facebook\FacebookDriver::class);
$adapter = new FilesystemAdapter();
$botman = BotManFactory::create($config, new SymfonyCache($adapter));
$botman->hears(
    'menu',
    function (BotMan $bot) {
        $bot->reply(
            Question::create(
                "Welcome to MENU, ".$bot->getUser()->getFirstName()
                ." 🙋\n\nPlease select below 👇"
            )->addButtons(
                [
                Button::create('🧠 Search wiki')->value('wikip')
                ]
            )
        );
    }
);
$botman->hears(
    'wikip',
    function (BotMan $bot) {
        $bot->startConversation(new Wikipedia);
    }
);
$botman->fallback(
    function ($bot) {
        $bot->reply(
            'Sorry, I did not understand these commands.'
        );
        $bot->reply(
            ButtonTemplate::create(
                'To go to menu please click below 😇'
            )
            ->addButton(
                ElementButton::create(
                    'Menu'
                )
                ->type('postback')
                ->payload('menu')
            )
        );
    }
);
$botman->listen();
