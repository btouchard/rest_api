Rest Api
========

JSON-REST API for Database and File access
With this API you can access and manage Tables databases and File System


Simple configuration
--------------------

Edit 'config.php' file for change debuging, mysql and auth configuration.


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


Minimal User table for authentication
-------------------------------------
``` SQL
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `expire` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
```

Using api via an app
--------------------

1) First authenticate by posting username and password data (configured in config.php file) to url http://server.com/signin
   If success, return needed token for all query in result 
   ```{'success':true, 'result': { 'token': 'azerty'}}```

2) Next, use this token for query api, by adding it to query header
```
   Ex: GET http://www.domain.com/matable
       X-APP-TOKEN: azerty
   Result: {"success":true,"result":[{"id":"1","code":"XXXXXX"},{"id":"2","code":"YYYYYY"},...]}
```

3) After use, you can disconnect by using : http://server.com/signout