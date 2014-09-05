Rest Api
========

JSON-REST API for Database and File access
With this API you can access and manage Tables databases and File Systèm

Working with HTTP VERB
----------------------

GET : List table or directory content
      Filter table field (ex: table_name?field=%@mail.com show all content with table field named 'field' liking '%@mail.com'
      Retreview File content if you specifing existing file path.

POST : Insert data in table
       Adding file to directory

PUT : Update data in table
      Pass 'name' argument for renaming file
      Update file (change content)

DELETE : Delete table or file/directory entry
