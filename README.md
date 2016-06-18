phpbb-ext-videos
================

phpBB 3.1 extension, that adds a url field when posting a new topic and auto embeds the given media before the first post.

## Features

* Post an url (with oembed support) when posting a topic
* The media is embedded before the first-post on each page of the topic
* enabled/disable on a per forum basis
* caches oEmebed API results (TODO ACP module allows pruning the cache)
* embed media of providers that support oEmbed, e.g.:
  * Video
   * Youtube
   * Vimeo
   * Daily Motion
   * ...
  * Audio
   * SoundCloud
   * ... 
  * and many more: http://oembed.com/#section7


## Installation

### 1. clone
Clone (or download an move) the repository into the folder phpBB3/ext/robertheim/videos:

```
cd phpBB3
git clone https://github.com/RobertHeim/phpbb-ext-videos.git ext/robertheim/videos/
cd ext/robertheim/videos/
composer install
```

### 2. activate
Go to ACP -> tab customise -> Manage extensions -> enable RH Videos  
Go to ACP -> Forums -> edit/create any forum -> set *Enable RH Videos* to *Yes*

### 3. configure

Goto ACP -> Extensions -> RH Videos

## Support

TODO link to phpbb.com topic
