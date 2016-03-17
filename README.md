# PHP-SPRX Authentication
This is a basic PHP authentication app for SPRX/PS3 applications. Fill in proper credentials, setup on server, GREAT SUCCESS!

##How to install:
1.Download zip and extract contents to a location on your PC

2.Open up `autoload.php` and add a name for the DATABASE_NAME constant at the top.

3.In the same file, add the credentials for your MySQL login. This user should have permissions to create tables and databases (so ideally use root)

4.Save file, and upload all the files to a directory on your server.

5.Go to the directory in your browser and load the setup.php script first.

6.If you used proper MySQL credentials, it will successfully create both a database and two tables in said database.

7.DELETE setup.php from your server

8.Create a new MySQL user with only the required permissions (SELECT, INSERT, UPDATE, etc) and replace your root user's credentials inside `autoload.php` with this new MySQL user information (thanks to JB for catching this error)

9.To test, access auth.php with a GET request for key using your NGU Elite key (ex: http://tusticles.com/auth/auth.php?key=MY-ELITE-KEY)

10.If the setup worked properly, it should output "Some useful information" (hilarious, right?) and if you check your log and users table, you should see your Elite key there.
