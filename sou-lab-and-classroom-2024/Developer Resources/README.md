Some things are not done and will need to be fixed and added.
Docker secrets are not implemented but were requested by the school's infrustructure team.

Study the Docker compose file to customize the naming and how you want it.
Look into the commands to delete volumes from docker to reload the database from the SOULABCLASSBACKUP.sql file

The Admin/Lab and class employee side of the program is all in CSV and Search folders

The front facing stuff for non-IT, Students, Faculity, and Guests at the University are in the Guest Folder and are all Mobile Friendly

The page Reserved for Super Admins is titled Admin Management on the main admin page and titled Admin.php inside of Search Folder.
When making a new Admin the User Name must be a @sou.edu email.

The main.php is inside of the main/src directory and is meant for admins and super admins

Pay close attention to the database documentation some names within the database will not be the ones on the actual site. Such as ProgramName, in the db, and Software Name on the site.

the current .env file has the parts to pass inside of the docker containers for the database and the google api for Oauth2 to change to your own go to google cloud console and api's and find the O auth 2 api in your account and go through the steps then replace my id and secret with yours

There is likely alot of bugs that need fixing, there is likely features ulrich wants too.
Some suggested features are QR code creation for each indivdual pieces of Equipment, Making the admin side work on mobile devices, adding a picture table for to map to the Equipment or simply Look at the model and make it pull the first image on google images.

This was a pleasure to create and work on Porter, Faisal, Peter. Class of 2024

