# SCFA/FAF Unit List
This project aims to create a webpage that can load dynamically Supreme Commander's unit list and display them all with their own stats.

### Prerequisites

What things you need to run the webpage :

####    Serverside :
```
Apache2 [but nginx works probably too)
PHP 5.6 or 7
```
If you have a LAMP server, it's more than enough.

####     Clientside :
```
Any browser capable of rendering HTML5 and executing basic javascript.
Tested with Vivaldi and with Chrome.
```

### Installing

Just download all the files and put them in your server's folder. 
With a LAMP server it'll be something like :
```
/var/www/html
```
## Authors

* [rackover@racknet.noip.me](https://github.com/Rackover)

Thanks to :
* biass & to amelieUntitle  for their design tips, even if I didn't follow much (sorry!)
* AchievedJaguar8, 
  JaggedAppliance, 
  PhilipJFry, 
  dm, 
  speed2, 
  JJ, 
  Exotic-retard, 
  Petric, 
  and Zook for general help and feedback

## License

This project is licensed under the Beerware license. See the [LICENSE](LICENSE) file for more details.

## Acknowledgments

* This database uses some sprites and logo from [Spooky's DB](https://github.com/spooky/unitdb) which was a similar project : but all the code for this one db has been made from scratch by me (rackover).
* Also, all the sprites and logos and fonts used in this project either come directly from the game files, or are vanilla Windows fonts.

## Todo list

For now the thing loads data from a [UNIT.JSON](UNIT.JSON) file, which needs to be generated from the game blueprints using tools from Spooky's repository. Next thing to do is to make them load directly from the game files ! Which should be a big thing. We'll see.
