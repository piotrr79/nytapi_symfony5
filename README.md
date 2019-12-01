### Symfony 5 based API for importing books from NYT api, saving them to DB and searching for books in DB

#### Api routes:
/api/books                
/api/authors              
/api/import   

#### Run:
bin/symfony server:start

#### cUrl / Postam calls:
curl -X GET \
  'http://127.0.0.1:8000/api/books?page=1&filters[search]=any&filters[author]=any&filter[reviews]=1' \
  -H 'Auth-Key: nytApi-12345'

  curl -X GET \
    'http://127.0.0.1:8000/api/authors?page=1' \
    -H ': ' \
    -H 'Auth-Key: nytApi-12345'

    
  curl -X GET \
  http://127.0.0.1:8000/api/import \
  -H 'Accept: */*' \
  -H 'Accept-Encoding: gzip, deflate' \
  -H 'Auth-Key: nytApi-12345' \
  -H 'Cache-Control: no-cache' \
  -H 'Connection: keep-alive' \
  -H 'Content-Length: 23' \
  -H 'Content-Type: text/plain' \
  -H 'Host: 127.0.0.1:8000' \
  -H 'cache-control: no-cache' \
  -d '{
	"author" : "Stephen King "
}'
