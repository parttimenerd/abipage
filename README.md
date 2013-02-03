#Abipage

An easy to setup web page, helping the creation of a german pupils "Abizeitung".
An Abizeitung is a little book german pupils traditionally create after their final exams. 

For more information an help, please visit the [wiki](https://github.com/parttimenerd/abipage/wiki).

##History
This project started in the middle of July 2012 to simplify the task of collecting
content for the Abizeitung with the help of web technologies. 
It is currently and used by the pupils of the LGÖ at [lgoe2013.julianquast.de](http://lgoe2013.julianquast.de).

##Usage
If you like to run the web site on your own server,
visit the [wiki](https://github.com/parttimenerd/abipage/wiki) for some information
and please contact me so I know who's working with this project and so I'm able 
to help.

##Development
You need to have nodejs and ruby being installed on your PC to develop this project.
Before you start to develop it, please run the init_tools.bat (or call the following commands
on your console: `npm install -g less coffee-script uglify-js cssmin`
and ``em install watchr colored`). Then run the watchr.rb while you edit the css, less, js or coffee files,
as the watchr.rb script automatically compiles and minimizes the files.

##TODO
- extend wiki (write some pages)
- create an home page for this project