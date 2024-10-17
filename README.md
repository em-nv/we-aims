STEP 1: Clone the Repository. Alternatively, download the zip file by clicking "Code" and choose "Download Zip."
STEP 2: Start Apache and MySQL
        2.1. Open the XAMPP Control Panel.
        2.2. Start Apache by clicking the 'Start' button next to Apache.
        2.3. Start MySQL by clicking the 'Start' button next to MySQL.
        2.4. To confirm Apache is running, open a browser and type `http://localhost/dashboard/`. Alternatively, click the 'Admin' button next to Apache in the XAMPP Control Panel.
        2.5. To confirm MySQL is running, open a browser and type `http://localhost/phpmyadmin/`. Alternatively, click the 'Admin' button next to MySQL in the XAMPP Control Panel.
STEP 3: Set Up the Database
        3.1. Open your browser and go to `http://localhost/phpmyadmin/`.
        3.2. Click on 'Databases' on the main toolbar or click 'New' in the side toolbar.
        3.3. Name the new database `we-aims` and click 'Create'.
        3.4. Click on the `we-aims` database from the left sidebar to open it.
        3.5. Click the 'Import' button on the main toolbar.
        3.6. Click 'Choose File', locate the `we-aims.sql.gz` file on your local computer, and select it.
        3.7. Scroll down and click the 'Import' button to import the database schema and data.
STEP 4: Launch the Site
        4.1. Open your browser and go to `http://localhost/dashboard/`.
        4.2. Change `dashboard` in the URL to `we-aims` and press Enter (i.e., go to `http://localhost/we-aims`).
        4.3. The website should launch.




