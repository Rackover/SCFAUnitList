# SCFA/FAF Unit List
This project aims to create a webpage that can load dynamically Supreme Commander's unit list and display them all with their own stats.
At the moment (17/01/2018), two other databases exist :
- The "official" one, which is very old and outdated.
- [Spooky's database](https://github.com/spooky/unitdb), up to date, but I personally don't like the layout of it. 
So I've made my own !

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

* All the code for this one db has been made from scratch using nothing more than my bare hands : no code have been taken neither from  [Spooky's DB](https://github.com/spooky/unitdb), nor from the *official* unitDB.
* All the sprites and logos and fonts used in this project either come directly from the game files, or are vanilla Windows fonts (except for the FA Forever logo, used as a favicon). But I must credit [Spooky's DB](https://github.com/spooky/unitdb) nevertheless, as I was too lazy to rip some game textures and got them directly from Spooky's git.

## Todo list

For now the thing loads data from a [UNIT.JSON](UNIT.JSON) file, which needs to be generated from the game blueprints using tools from Spooky's repository. Next thing to do is to make them load directly from the game files ! Which should be a big thing. We'll see.
