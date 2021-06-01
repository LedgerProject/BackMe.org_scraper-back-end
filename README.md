# rewrited.news
## Everyone is accountable.
### Codename: Aletheia

This project aims to track and fight fake news.   
It is a tool for the public that allows in an easy way to check if/when/how a news has been edited.
You can check it in action at [rewrited.news](https://rewrited.news/)

## Structure

The application has 3 main components:
* frontend (just for demo now, a more complex one is planned for the future)
* REST API (search/scrape/store)
* crawler (this will run costantly in background)  

### Frontend

The scraper use a custom modules for each source (website) configured (set in the /scraper/config.php file).  
You can write your own module, put it into /scraper/modules/ the naming of the module must be: the domain name of the source without "www" and the dot replacing the dot with an underscore (i.e. a module for https://cnn.com would be named cnn_com.php), than add the modules detail into the /scraper/config.php file under the "sources" array (follow the directions in the comment in there).
From the homepage you can access a page dedicated to each source just clicking on its logo, there you can have an overview with the latest articles and the revisions.
Every article has its page with an intuitive structure to see the original version and the following revisions, users can share with a simple click an image of any revision.

### REST API

The scraper flow is the following: check if the website is supported, if so it scrapes the page content, than it checks if it is already present a version on the database and saves it as revision if there isn't one or if there is one but the content is different.
To improve performances the matching of the content is made by hashing it.

### Crawler

The crawler will check periodically new articles from the source configured, the crawler updater will check out the articles already stored for revision with a decreasing frequency, i.e. for the first two days after the publication it will check the article every hour, from the third day it will check every six hours, after a week every day and after a month every 15 days.

## Blockchain

Periodically Aletheia, via ZenRoom (setup on the same server via Docker), stores the scraped contents into the blockchain (SawRoom).
It uses RestRoom to call /scraper/latest and get the hashes of each revision, it stores them and then it calls /scraper/confirm to update the status in the db.
You can find in /zencode the scripts to do so.

## Immutable database

To ensure the integrity of the data we are planning to integrate the latest version (release in May 2021) of [ImmuDB](https://github.com/codenotary/immudb), an immutable database. This will guarantee that Aletheia can't edit the data stored.

## DOCS

### Crawler

To setup the crawling you can use cron jobs: execute the /scraper/crawler.php file, it accepts two parameters, the api key (set in the config to avoid unauthorized access) and optionalli the frequency (if "all" retrives the current feeds and put them in the queue, if empty it scrapes the daily articles and if numeric the N day's old articles 1,2,3,7,15,30)

### Blockchain saving

You can use a cron jobs to set the frequency of the blockchain transactions executing /scraper/crawler.php, it requires one parameter: the api key (set in the config to avoid unauthorized access). This process trigger a ZenRoom API that gets a queue of revisions not stored yet and save them, when done it marks them as saved and release the process for another iteration.

### More
Documentation still in progress ◔_◔

## Third-party components

Aletheia backend uses:  
* [ZenRoom](https://https://zenroom.org/) under AGPL3 licence  
* [SawRoom](https://sawroom.dyne.org/)
* [ReadAbility](https://github.com/andreskrey/readability.php) under Apache License
* [Simple-html-dom](http://sourceforge.net/projects/simplehtmldom/) under MIT licence 

Aletheia frontend uses:  
* [jQuery](https://jquery.org/license/) under MIT licence  
* [Bootstrap](https://getbootstrap.com/docs/4.0/about/license/) under MIT licence  
* [DOM to Image](https://github.com/tsayen/dom-to-image) under MIT licence
* [data-uri-to-img-url](https://github.com/aminariana/data-uri-to-img-url)