Wikipedale ?
=============

Wikipedale est un logiciel de signalement de problèmes à Vélo. Il est développé par le [GRACQ ASBL](http://www.gracq.org) avec le soutien de la Région Wallonne.

Le projet est en test à l'adresse suivante http://uello.be.



Installation 
-------------

*Minimum requirements*

- A postgresql >= 9.1 + postgis >= 2.0 database
- php 5.5
- an Unix system (Linux, Mac Os)

You will also need some geographical information about the zone you want to survey.

*Prepare the database*

Create a postgresql database and enable postgis:

```sql

CREATE EXTENSION postgis;

```

*Install the app and dependencies*

```bash
git clone <url> wikipedale
cd wikipedale
#get composer.phar
curl -sS https://getcomposer.org/installer | php
#install symfony + dependencies
php composer.phar install
```

At the end of the install's process, you will be prompted for the following informations :

```yaml

parameters:
    #db information :
    database_driver   : pdo_pgsql #do not change this until you know what you are doing :-)
    database_host     : localhost 
    database_port     : 5432
    database_name     : databasename
    database_user     : username
    database_password : password
    
    #a lot of notification's mail are send by the software. Please fill this information
    mailer_transport  : smtp
    mailer_host       : 
    mailer_user       :
    mailer_password   :
    
    #until now, the software hasn't been translated into another languages than French
    locale            : fr

    #a random string
    secret            : ThisTokenIsNotSoSecretChangeIt
    

    #for localisation. If you speak french, do not change this
    date_format       : d/m/Y à H:i
    
    #the cities which should appears on the front page. You may change this later. Cities may refer to an entry of the zones table (see below). 
    cities_in_front_page: [mons, tournai, liege, walhain, namur]

    #this is the information of the type of each report
    place_types       :
      bike : #bike si a generic key
            label : place_type.bike.label #the label of the type
            terms : #if you want to have different term (short term, long term, etc.)
               - {key: short, label: place_type.bike.short.label, mayAddToPlace: IS_AUTHENTICATED_ANONYMOUSLY}
               - {key: long, label: place_type.bike.long.label, mayAddToPlace: ROLE_PLACE_TERM }
               - {key: medium, label: place_type.bike.medium.label, mayAddToPlace: IS_AUTHENTICATED_ANONYMOUSLY }
                  
    place_type_default: 'bike.short'

```

In case of error you can edit the parameters file */app/config/parameters.yml*.

*Prepare the database'schema*

```sh

php app/console doctrine:migrations:migrate

```

*Configure web server and permissions*

The web server should point to the `wikipedale/web` directory. For test and prod environments, the software will be reachable by running `http://localhost/path/to/wikipedale/web/app_dev.php`.

You may also use the embedded php server : `php app/console server:run`.

The directories `app/cache` and `app/logs` must be writeable by the apache user *AND* the user you will use to run php app/console commands.

See [Symfony documentation](http://doc.symfony.com/to_do) for doing this.

*basic data*

_create an admin user_

```bash

php app/console fos:user:create admin --super-admin

```

_add data for zones_

Until now, we must introduce geographical zones to use the software. These zones are used :

- on the front page, to let the user go the zone he wants. In Wallonia, we introduced municipalities as zones ;
- the software decide who is responsible for the report by those queries: "in which zone is the report" and "who is responsible for this zone (if any) ?"

It is very important to create zones ! You must provide your own zone, based upon the geographical information you have. This may be so different from one region in the world to another, and we do not provide an "one-line-step" to record them into the database.

Here are the fields in the database :

```sql

CREATE TABLE zones
(
  id integer NOT NULL,
  name character varying(60) NOT NULL, -- the name of the city, this will appears for user
  slug character varying(60) NOT NULL, -- the slug is used in URL's. It must be unique for each zone
  codeprovince character varying(4) NOT NULL, -- 4 letters, used to make queries for reporting. Not used by the software. May be an empty string
  polygon geography(Polygon,4326) NOT NULL, --  very important ! the geographical polygon of the zone. Note the CRS
  center geography(Point,4326) NOT NULL, -- the geographical center of the above's polygon. The UI will center there
  type character varying(5) NOT NULL, -- a 5-character string to indicate the kind of zone. 
  CONSTRAINT zones_pkey PRIMARY KEY (id)
)
```

Then you have to add the slug of the zones that you want to see in front page
in the array *cities_in_front_page* contained in the file *app/config/parameters.yml*.

*add categories and groups*

At this point, you should be able to go to the admin zone `http://<your url>/admin` and fill categories.

You may also use the command `php app/console uello:categories` to import some uello's categories.

For moderators and manager, you may create some groups in the admin zone: 

- create the groups ;
- create the notations ;
- add users to groups (when you will have users :-), see below )

*adapt the UI*

You may adapt the UI by copying the src/Progracqteur/WikipedaleBundle/Resources/views in app/Resources/views and the adapting them.

*start to use*

Create users (either by the front-end (email validation required) or by running `php app/console fos:user:create`.


Documentation
-------------

Le wiki du projet explique le fonctionnement de l'API, les différentes URL disponibles, etc.
