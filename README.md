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

For up-to-date unit data you will have to provide your own data (game files .SCD or .NX2). More info in the "Installing" part of this readme.

####     Clientside :

Any browser capable of rendering HTML5 and executing basic javascript.
Tested with Vivaldi, Firefox and with Chrome.

Firefox kind of lags a bit due to the huge amount of elements on the page : disabling previews in the settings can help reducing lag on some browser.

### Installing

First download all the files and put them in your server's folder. 
With a LAMP server it'll be something like :
```
/var/www/html
```
You can put them pretty much wherever you want, as most of the code uses relative paths.

Once you've done that, you can edit the `CONFIG/DATAFILES.JSON` file to link the database to the game files. By default, the game is linked to .3599 files in the `DATA/GAMEDATA` folder. You can add more files in the list, and they will be loaded in the order specified. Two things to note here :
* Keep in mind that uncompressing data and analyzing it will be done in PHP, therefore these operations must not take more than *120 seconds* combined : else it will break and exit before writing any change. 
* You should always keep the .3599 file to be loaded somewhere (in first) as it is very complete, and if the data you load after it lacks unit, the unitDB will fall back to 3599 files. If you don't mount these files before anything else, stuff will probably be missing. 

`CONFIG/LOCFILES.JSON` works the same way, but for localization files (.scd).

Once you're done editing and tweaking stuff, run `update.php` in your browser (or `update.php?debug=1) and everything should be fine.

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
  MrShiny1,
  and Zook for general help and feedback

## License

This project is licensed under the Beerware license. See the [LICENSE](LICENSE) file for more details.

## Acknowledgments

* All the code for this one db has been made from scratch using nothing more than my bare hands : no code have been taken neither from  [Spooky's DB](https://github.com/spooky/unitdb), nor from the *official* unitDB.
* All the sprites and logos and fonts used in this project either come directly from the game files, or are vanilla Windows fonts (except for the FA Forever logo, used as a favicon).