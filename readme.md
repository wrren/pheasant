# Introduction

Pheasant Market Trade Processor. Accepts incoming currency trade information encoded in JSON through POST to the trade controller and records it in the configured MySQL database after decoding and validation. Implements two-tier rate limiting; all requests can be rate-limited based on IP Address while trade submissions can be rate-limited based on the user ID of the trade. 

After successful storage an event is published through Redis to the Trade Event Server. The Trade Event Server accepts incoming web socket connections and sends new trades to connected clients on receipt of Redis events. For an example of a trade feed display front-end based on Google's WebGL Globe, see [Peacock](http://github.com/wrren/peacock).

# Installation

## Dependencies

### System Level

- PHP 5.5+
- MySQL 5+
- Redis
- PHP MCrypt Extension
- [PHP iredis Extension](https://github.com/nrk/phpiredis)


### Composer Dependencies

- Laravel 5
- Predis/Predis Async
- Ratchet
- React
- Silex

```
git clone git@github.com:wrren/pheasant.git
cd pheasant
composer install
```

# Configuration

Edit ```config/database.php``` to set up database and redis configuration. Rate limiting may be configured in ```config/rate.php```. Rate limiting factors indicates the rate limiters which should be used for incoming requests. Both ```ip``` and ```user``` settings share the configured window length and request limit settings. Set the ```factors``` settings to an empty list (```[]```) to disable rate limiting.

Bootstrap your MySQL database with the trade schema using the ```sqerl.sql``` file included in the repository.

```
mysql -u <user> -p <database> < sqerl.sql
```

# Running the Web Socket Server

The Trade Event Server is configured as an artisan command. From the project root, run ```php artisan trade:wsserver```. The web socket listen port may be configured using the ```--listen``` option and is set to port 8081 by default. The server subscribes to the ```trade.event``` channel and receives trade data encoded as JSON from the ```TradeController``` after validation and storage. Trade JSON data is passed unedited to connected clients. To run the server in the background or let it persist after logout, use ```screen``` or generate an init script for your OS.

```screen -S trade.server php artisan trade:wsserver```

# Interacting with pheasant

Trade data should be POST'ed to the ```/trade``` route with a content type of ```application/json```. To test trade recording, use the ```request.sh``` script included with the repo. The first argument to the script is the trade submission URL. When running on the same server with the default listen port, the address would be ```http://localhost:8080/trade```. Request frequency is set to 10/s.

To receive trade submission events over websocket, connect on the port specified during server startup. If using  [peacock](https://github.com/wrren/peacock), edit ```public/app/js/config.js``` and set the websocket URL. Example configuration:

```
var config = { server: "ws://example.com:8081", max_feed_items: 30 };
```
