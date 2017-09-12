meta-search-engine
==================

This is a meta search engine to join the forces of several Schul-Cloud search engines.

This is part of the minimal working prototype.
This README file must explain, 

- how to use the search engine and how to configure it, in the [How To Section][use],
- how to install the search engine, in the [Installation Section][installation]
- how to work on it, in the project guidelines
- what else it is related to, in the [Related Work Section][related-work]

Related Work
------------
[related-work]: #related-work

You can view the [issue for the creation of this search engine][issue].

How To Use
----------
[use]: #how-to-use

You can use this search engine using different means:

You can use Docker to run the search engine.
Therefore, you can pass the arguments of which searches to join to the docker command.

As an example, you want to see how your search engine performs when it would be used inside the
official Schul-Cloud search. Your search engine runs on http://localhost:8080 and the
Schul-Cloud search runs at http://search.schul-cloud.org.
You can run the docker-container at port 9999.
```
docker run schulcloud/meta-search-engine --url http://localhost:8080 --url http://search.schul-cloud.org --port 9999
```
Now, if you request a search for Einstein at http://localhost:9999/v1/search?Q=Einstein, you should see your
results along the other results.

Likewise, if you install the seach engine locally, you can use the local installation to run the search:

    schul-cloud-meta-search-engine --url http://localhost:8080 --url http://search.schul-cloud.org --port 9999

Installation
------------
[installation]: #installation

 - Install apache and PHP as mentioned in [this guide][install-apache].  
   Under Ubuntu, do this:
   ```
   sudo apt-get update
   sudo apt-get -y install apache2
   ```
   Check that http://localhost returns a webpage.
   ```
   sudo apt-get -y install php libapache2-mod-php
   ```
 - Get this repository.  
   You can download the sources or clone it usig git.
   ```
   git clone https://github.com/schul-cloud/meta-search-engine.git
   cd meta-search-engine
   pwd # /path/to/meta-search-engine
   ```
 - Create a symlink to your location from for http://localhost/v1/search  
   Therefore you need the path from pwd.
   ```
   sudo mkdir -p /var/www/html/v1
   sudo ln -sT /path/to/meta-search-engine/src /var/www/html/v1/search
   ```
   
   





[issue]: https://github.com/schul-cloud/schulcloud-content/issues/2
[install-apache]: http://www.allaboutlinux.eu/how-to-run-php-on-ubuntu/
