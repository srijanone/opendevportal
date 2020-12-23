CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Configuration

INTRODUCTION
------------

This module provides a field formatter for File and Link fields which renders
the referenced file using RapiDoc UI.

CONFIGURATION
-------------

File fields:

    1. Navigate to Structure > Content types > TYPE > Manage fields where
       TYPE is the content type to which you want to add the new field, such as
       a Basic page.
    2. Click on the "Add field" button to add a new field.
    3. Set the field type to "File" and enter a label name.
    4. Click "Save and continue".
    5. On the "Edit" tab, in the "Allowed file extensions" field enter the
       following: json
    6. Click "Save settings".
    7. Click on the "Manage display" tab.
    8. Select "RapiDoc UI" in the Format drop-down for the new field and
       optionally configure formatter settings.
    9. Click "Save".
    10. Add a new TYPE type content and upload a valid Swagger json file.