Tymio
=====

Installation
------------

* Install PHP5, Apache2 and MySQL on your server
* Clone the repository into your `htdocs` directory
* Create a database and load the `res/db/schema.sql` file
* Create a `config.ini` file (see `config.template.ini`) and include your database credentials
* Open the host in your browser and login as `admin`. The default password is `tymio`


Mobile
------

* The mobile site is in the `mobile` sub-directory.
* Redirection is performed automatically by `index.php`.


Booking
-------

* Clockings are processed directly.
* Bookings are created by iXML scripts which define clocking rules specific to a
  company, organization or business unit.


iXML Customizations
-------------------

* Added stack trace information with line numbers and function names
* Changed semantics of nested blocks:
	- The inner blocks' resulting string contents are concatenated to obtain the
	  enclosing block's contents, i.e. you can do this:

		`<debug:output><t>Holiday #$holiday.Id, </t><date:format format="D, Y-m-d">$holiday.Date</date:format><t> $holiday.Name</t></debug:output>`
* Added a tag `<t>` which simply yields its contents.
  This tag is necessary because we still remove plain text nodes, unless the text node is the only content within a block, as the original iXML does.
* Added various other tags specific to tymio.


Notice about external libraries
-------------------------------

External libraries are included in the `lib` directory. This means they are not
actively updated or managed through composer or gitmodules. If you manually want
to check for newer versions, you can find links the most important libraries here:

* [Xily](https://github.com/dapepe/xily): XML-based templating engine
* [Propel](https://github.com/propelorm/Propel): Database OR mapper
* [iXML](http://www.ixmldev.com): XML based scripting language (see [www.ixmldev.com](http://www.ixmldev.com))
* [REST](https://github.com/zeyosinc/rest): RESTful HTTP API client and server

Please keep in mind: If you consider updating individual libraries, you will probably
run into compatibility issues. Especially Xily and iXML will not work with newer versions.


Contributions
-------------

Tymio is not a commercial product. Development is purely user-driven, so in case
you want to contribute, you are very welcome to add your own pull requests on GitHub.


License
-------

[GNU AFFERO GENERAL PUBLIC LICENSE](http://www.gnu.org/licenses/agpl-3.0.en.html)
