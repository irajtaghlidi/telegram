<?php

namespace NotificationChannels\Telegram;

class TelegramMessage
{
    /**
     * @var string content type.
     */
    public $type = 'text';

    /**
     * @var array Params payload.
     */
    public $payload = [];

    /**
     * @var array Inline Keyboard Buttons.
     */
    protected $buttons = [];

    /**
     * @param string $content
     *
     * @return static
     */
    public static function create($content = '')
    {
        return new static($content);
    }

    /**
     * Message constructor.
     *
     * @param string $content
     */
    public function __construct($content = '')
    {
        $this->content($content);
        $this->payload['parse_mode'] = 'Markdown';
    }

    /**
     * Recipient's Chat ID.
     *
     * @param $chatId
     *
     * @return $this
     */
    public function to($chatId)
    {
        $this->payload['chat_id'] = $chatId;

        return $this;
    }

    /**
     * Notification message (Supports Markdown).
     *
     * @param $content
     *
     * @return $this
     */
    public function content($content)
    {
        $this->payload['text'] = $content;

        return $this;
    }


    /**
     * add File to Message
     *
     * @param string $file
     * @param string $type
     * @param string $filename
     *
     * @return $this
     *
     */
    public function file($file, $type, $filename = null)
    {
        $this->type = $type;

        if(is_file($file))
        {
            $this->payload['file'] = ['name' => $type, 'contents'=> fopen($file, 'r')];
            if($filename)
                $this->payload['file']['filename'] = $filename;
        }
        else
            $this->payload[$type] = $file;

        return $this;
    }

    /**
     * Add an inline button.
     *
     * @param string $text
     * @param string $url
     * @param int   $columns
     *
     * @return $this
     */
    public function button($text, $url, $columns = 2)
    {
        $this->buttons[] = compact('text', 'url');

        $replyMarkup['inline_keyboard'] = array_chunk($this->buttons, $columns);
        $this->payload['reply_markup'] = json_encode($replyMarkup);

        return $this;
    }

    /**
     * Additional options to pass to sendMessage method.
     *
     * @param array $options
     *
     * @return $this
     */
    public function options(array $options)
    {
        $this->payload = array_merge($this->payload, $options);

        return $this;
    }

    /**
     * Determine if chat id is not given.
     *
     * @return bool
     */
    public function toNotGiven()
    {
        return !isset($this->payload['chat_id']);
    }

    /**
     * Returns params payload.
     *
     * @return array
     */
    public function toArray()
    {
        $this->contentKey();

        return $this->payload;
    }


    /**
     * Create Multipart array
     *
     * @return array
     *
     */
    public function toMultipart()
    {
        $this->contentKey();

        $data = [];
        foreach ($this->payload as $key => $value)
        {
            if($key != 'file')
            {
                $data[] = ['name' => $key, 'contents' => $value];
            }
            else
            {
                $data[] = $value;
            }
        }
        return $data;
    }


    /**
     * Choose between ['text'] and ['caption'] for payload
     */
    public function contentKey()
    {
        if($this->type != 'text')
        {
            $this->payload['caption'] = $this->payload['text'];

            $this->payload = array_except($this->payload, ['text']);
        }
    }
}
