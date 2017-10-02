meta-search-engine
==================

[![Build Status](https://travis-ci.org/schul-cloud/meta-search-engine.svg?branch=master)](https://travis-ci.org/schul-cloud/meta-search-engine)
[![Docker Build Status](https://img.shields.io/docker/build/jrottenberg/ffmpeg.svg)](https://hub.docker.com/r/schulcloud/meta-search-engine/builds/)

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

Likewise, if you install the seach engine locally,
you can edit the [search-engines.txt][list] file.
Each line contains a search engine.


Query
-----

If you run the search engine, you can also specify which search engines should
be requested.
If a usual query looks like `Q=einstein`, a query requesting an additional
search engine running under http://localhost:8080/v1/search looks like
`Q=einstein&Search=http://localhost:8080/v1/search`.

You can request several additional search engines by adding a number behind
the `Search` parameter:

    Q=einstein
    Q=einstein&Search1=http://localhost:8080/v1/search
    Q=einstein&Search1=http://localhost:8080/v1/search&Search2=http://localhost:8080/v1/search
    ...

If you do not like to request the default search engines,
you can use the `Default=false` parameter.
Then, only the search engines passed with `Search` query parameters are
requested.

- `Q=einstein` will request all search engines in the
  [search engine listing][list].
- `Q=einstein&Default=false` will request no search engine.
  


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
   sudo apt-get -y install libapache2-mod-php curl \
                           libcurl3 libcurl3-dev php-curl
   ```
 - Get this repository.  
   You can download the sources or clone it usig git.
   ```
   git clone https://github.com/schul-cloud/meta-search-engine.git
   cd meta-search-engine
   pwd # /path/to/meta-search-engine
   ```
 - Create a symlink to your location for http://localhost/v1/search  
   Therefore you need the path from pwd.
   ```
   sudo mkdir -p /var/www/html/v1
   sudo ln -sT /path/to/meta-search-engine/src /var/www/html/v1/search
   ```
   Now, you should be able to access http://localhost/v1/search

Docker
------

You can install docker.

    wget -O- https://get.docker.com | sh
    sudo usermod -aG docker $USER
 
To enable that you do not need `sudo` to run the docker containers,
log in and out.

Once you installed docker, you can create a new `schulcloud/meta-search-engine`
container like this:

    docker build -t schulcloud/meta-search-engine .

Then, you can run the container and map the http port to port 8000.

    docker run --rm -p 8000:80 schulcloud/meta-search-engine

Now, you should be able to request a search:

    curl -i 'http://localhost:8000/?Q=einstein'

[issue]: https://github.com/schul-cloud/schulcloud-content/issues/2
[install-apache]: http://www.allaboutlinux.eu/how-to-run-php-on-ubuntu/
[list]: src/search-engines.txt
