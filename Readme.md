# Loek

This is an implementation of Elo to rate onions, it was made as an inside joke but can be reused my anyone.

The [Elo rating system](https://en.wikipedia.org/wiki/Elo_rating_system) was used to rank the images.

`images.sql` is the sql creation file for the "images" table that the php code needs. 

The scripts folder has some php scripts used to populate the table with links to images.

### Steps to deploy the website
1. Install Apache, MySQL, PHP and [Composer](https://getcomposer.org/).

2. Copy the `.env.example`and rename it to `.env` and change the values in it 

3. Import the images table from `images.sql`. This can be done in multiple way depending on how you communicate with your database.

4. Add links to images using the php scripts in the scripts folder.
    * `enterdata.php is used to enter all the links into the database.
    Run this in the linux commandline as `php enterdata.php < linkfile.txt`

5. Run `composer install` or `php composer.phar install` depending on your install 

6. Start apache and visit your page.
