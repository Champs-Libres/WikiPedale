Wikipedale ?
=============

Wikipedale est un logiciel de signalement de problèmes à Vélo. Il est développé par le [GRACQ ASBL](http://www.gracq.org) avec le soutien de la Région Wallonne.

Le projet est en test à l'adresse suivante http://uello.be.

Concepts
---------

The story of a point :

An **user** introduce a problem in the system: this "problem" will be named a "**place**" in the development language. 

The users may introduce the points without being registered. In this case, the user will be asked for an email address and a phonenumber. The email address will be checked by an confirmation email, and the point will not be shown on the map unless the user has confirmed the point.

If they are registered and authenticated, the users won't be prompted for his name, nor phonenumber, nor email.

The user is then "suscribed" to **notifications** on further changes made on the point by the **moderators**.

A **moderator's group** is chosen by the system. Currently, the system choose the **moderator's group** which is responsible for the point's zone. All **members** of the **moderators'group** receive an email which says that a new **place** has been introduced in the system.

A **member** of the **moderator's group** may record a **manager's group**, which will be responsible for resolving the problem on the field. When a **member** of a **manager's group** has finished to resolve the problems, he may warn the **moderator's group** by sending him a **private comment**. Moderators and managers may, at any time, use **private comments** for discussions.

**Moderators** indicate, amongst others, the status of the **place**, which we name a **notation**. The software offers the possibility to add different kind of notation to the place, but this possibility is not implemented at this moment. The **notation** may have three states : 

- rejected (color: grey, value -1), not shown on the map ;
- not taken into account by the moderator (color: blank, value: 0) ;
- taken into account (color: red, value: 1)
- a solution is planned (color: orange, value: 2)
- resolved (color: green, value: 3)

**Places** also have **categories**, which classify the type of the problem. Each 
**category** is associated with an estimated **term** that can be :
- short term : the problem may be resolved in a few time. 
- mid term: the problem is not so easy to resolve, but is reachable in a couple of month. i.e. : the blank mark on the street will be painted during the next painting campaign, which usually take place at Spring.
- long term : the problem require time and discussion before being resolved. Until now, only **moderators** may introduce long term problems, after a discussion with a municipal commission.

The users (including moderators and managers) may adapt the **notification** delay on their control panel. Notifications may be send immediatly (a cron must be introduced) or on a daily basis. 


Installation 
-------------

*Minimum requirements*

- A postgresql >= 9.1 + postgis >= 2.0 database
- php 5.5
- an Unix system (Linux, Mac Os)


Note that we never test the installation on Windows.
If you try and manage to install Uello on Windows please give us a feedback.


You will also need some geographical information about the zone you want to survey.

*Prepare the database*

Create a postgresql database. If you want to call the db `uello_db` and the db user `uello_user` :

```bash
createuser -P uello_user 
createdb uello_db -E UTF8 -O uello_user
```

Enable postgis:

```sql
CREATE EXTENSION postgis;
```

Give the property of the tables `geometry_columns` and `spatial_ref_sys` to the user that will use the db. 
If this user is `uello_user`:

```sql
ALTER TABLE geometry_columns OWNER TO uello_user;
ALTER TABLE spatial_ref_sys OWNER TO uello_user;
```

*Install the app and dependencies*

```bash
git clone https://github.com/GRACQ-dev/Wikipedale.git wikipedale # use the stable branch for test and prod
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
    
    #the cities which should appears on the front page. You may change this later. A City has to refer to the slug of an entry of the zones table (see below). 
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

In case of error you can edit the parameters file `/app/config/parameters.yml`.

*Prepare the database'schema*

```sh
php app/console doctrine:migrations:migrate
```

*Configure web server and permissions*

The web server should point to the `wikipedale/web` directory. For test and prod environments, the software will be reachable by running `http://localhost/path/to/wikipedale/web/app_dev.php`.

You may also use the embedded php server : `php app/console server:run`.

The directories `app/cache` and `app/logs` must be writeable by the apache user *AND* the user you will use to run php app/console commands.

See [the "setting up permissions" in the Symfony documentation](http://symfony.com/doc/current/book/installation.html#configuration-and-setup) for doing this.

*basic data*

_create an admin user_

```bash
php app/console fos:user:create admin --super-admin
```

You can edit the name of the user, and its email at `http://your-installation/profile

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
  url character varying(255) NOT NULL, -- the url of the website of the zone, if any. This will appears in the UI
  description text NOT NULL, -- a descriptions moderators would like to shown in the UI
  CONSTRAINT zones_pkey PRIMARY KEY (id)
)
```

For instance :

```sql
INSERT INTO zones (id, name, slug, codeprovince, polygon, center, type, url, description) VALUES (1, 'Mons', 'mons', 7000, ST_GeomFromText('POLYGON((3.7 50.55, 4.3 50.55, 4.3 50.4, 3.7 50.4, 3.7 50.55))',4326), ST_GeomFromText('POINT(3.95117 50.45417)',4326), 'city', 'mons.be', 'Description de Mons');
```

The `type` must be `city`.

Then you have to add the slug of the zones that you want to see in front page
in the array `cities_in_front_page` contained in the file `app/config/parameters.yml`.

*add categories and groups*

At this point, you should be able to go to the admin zone `http://<your url>/admin` and fill categories.

You may also use the command `php app/console uello:categories` to import some uello's categories.

For moderators and manager, you may create some groups in the admin zone: 

- create the groups ;
- create the notations ;
- add users to groups (when you will have users :-), see below )

*prepare cron jobs*

you must execute cron jobs to send notifications email. This cronjob must execute `php app/console wikipedale:notification:send 60 --env=prod` for minutely notification, and `php app/console wikipedale:notification:send 86400 --env=prod` for daily notifications.

*adapt the UI*

You may adapt the UI by copying the src/Progracqteur/WikipedaleBundle/Resources/views in app/Resources/views and the adapting them.

*start to use*

Create users (either by the front-end (email validation required) or by running `php app/console fos:user:create`.


Documentation
-------------

Le wiki du projet explique le fonctionnement de l'API, les différentes URL disponibles, etc.
