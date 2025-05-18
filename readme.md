# commande curl pour présenter l'API:

## livre

- curl.exe -X GET "http://localhost:8000/livres"
- curl.exe -X GET "http://localhost:8000/livres/1"
- curl.exe -X GET "http://localhost:8000/livres/create?isbn=9780451524935&date_parution=1949-06-08&auteur_id=3"
- curl.exe -X GET "http://localhost:8000/livres/create?titre=1984&isbn=9780451524935&date_parution=1949-06-08&auteur_id=1"
- curl.exe -X PUT "http://localhost:8000/livres/1?titre=1984+-+Édition+révisée&isbn=9780451524935&date_parution=1950-01-01&auteur_id=1"
- curl.exe -X PUT "http://localhost:8000/livres/1?titre=1984+-+Édition+révisée&isbn=9780451524935&date_parution=1950-01-01&auteur_id=1"
- curl.exe -X DELETE http://localhost:8000/livres/1
- curl.exe -X DELETE http://localhost:8000/livres/3
- curl.exe "http://localhost:8000/livres/search?auteur_id=1"
- curl.exe "http://localhost:8000/livres/search?titre=harr"
- curl.exe "http://localhost:8000/livres/search?isbn=1234"

## auteur

- curl.exe -X GET "http://localhost:8000/auteurs"
- curl.exe -X GET "http://localhost:8000/auteurs/1"
- curl.exe -X GET "http://localhost:8000/auteurs/3"
- curl.exe -X GET "http://localhost:8000/auteurs/create?nom=Tolkien&prenom=J.R.R.&date_naissance=1892-01-03&date_mort=1973-09-02"
- curl.exe -X GET "http://localhost:8000/auteurs/create?nom=Rowling&prenom=J.K.&date_naissance=1965-07-31"
- curl.exe -X PUT "http://localhost:8000/auteurs/1?titre=John+Tolkien&date_mort=1973-09-03"
- curl.exe -X DELETE "http://localhost:8000/auteurs/1"
- curl.exe -X DELETE "http://localhost:8000/auteurs/2"
- curl.exe "http://localhost:8000/auteurs/search?nom=tolk"
- curl.exe "http://localhost:8000/auteurs/search?prenom=j.k."
- curl.exe "http://localhost:8000/auteurs/search?date_naissance=1965-07-31"
- curl.exe "http://localhost:8000/auteurs/search?date_naissance=1900-05-31"


