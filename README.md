# Aletheia
## Everyone is accountable.

This project aims to track and fight fake news.   
It is a tool for the public that allows in an easy way to check if/when/how a news has been edited.

## Structure

The application has 3 main components:
* frontend (just for demo now, a more complex one is planned for the future)
* REST API (search/scrape/store)
* crawler (this will run costantly in background)  

### Frontend

The scraper use a custom modules for each source (website) configured (set in the /scraper/config.php file).  
You can write your own module, put it into /scraper/modules/ the naming of the module must be: the domain name of the source without "www" and the dot replacing the dot with an underscore (i.e. a module for https://cnn.com would be named cnn_com.php), than add the modules detail into the /scraper/config.php file under the "sources" array (follow the directions in the comment in there).

### REST API

The scraper flow is the following: check if the website is supported, if so it scrapes the page content, than it checks if it is already present a version on the database and saves it as revision if there isn't one or if there is one but the content is different.

### Crawler

The crawler will check out the websites and their articles with a decreasing frequency, i.e. for the first two days after the publication it will check the article every hour, from the third day it will check every six hours, after a week every day and after a month every 15 days.

## API

Documentation still in progress ◔_◔

## Third-party components

Aletheia scraper uses  
* [PHP Simple HTML DOM Parser](https://simplehtmldom.sourceforge.io/docs/) under MIT licence  

Aletheia frontend uses:  
* [jQuery](https://jquery.org/license/) under MIT licence  
* [Bootstrap](https://getbootstrap.com/docs/4.0/about/license/) under MIT licence  
