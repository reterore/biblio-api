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
- curl.exe "http://localhost:8000/livres/search?auteur=tolkien"
- curl.exe "http://localhost:8000/livres/search?titre=harr"
- curl.exe "http://localhost:8000/livres/search?isbn=1234"
- curl.exe -X POST "http://localhost:8000/livres/2/emprunter/1"
- curl.exe -X POST "http://localhost:8000/livres/2/rendre"



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

## client

- curl.exe -X GET "http://localhost:8000/clients"
- curl.exe -X GET "http://localhost:8000/clients/create?nom=Durand&prenom=Alice&adresse=12%20rue%20des%20Lilas,%20Paris"
- curl.exe -X GET "http://localhost:8000/clients/create?nom=Durand&prenom=Alice&email=alice.durand@example.com&tel=0000000000&adresse=12%20rue%20des%20Lilas%2C%20Paris&date_naissance=1990-06-25"
- curl.exe -X PUT "http://localhost:8000/clients/1?email=alice.nouvel@example.com&tel=1111111111"
- curl.exe -X DELETE "http://localhost:8000/clients/3"
- curl.exe -X DELETE "http://localhost:8000/clients/10"
- curl.exe "http://localhost:8000/clients/search?nom=Durand"
- curl.exe "http://localhost:8000/clients/search?adresse=Lyon"
- curl.exe "http://localhost:8000/clients/search?adresse=Belfort"
- possibilité de créer 2 nouveau clients et de les rechercher via la date du jour pour vérifier que les 2 apparaissent

## emprunts

- curl.exe -X GET "http://localhost:8000/emprunts"
- curl.exe -X GET "http://localhost:8000/emprunts/1"
- curl.exe -X GET "http://localhost:8000/emprunts/3"
- curl.exe -X GET "http://localhost:8000/emprunts/create?client_id=1&livre_id=1&date_emprunt=2024-05-20"
- curl.exe -X GET "http://localhost:8000/emprunts/create?client_id=1&livre_id=1&date_emprunt=2024-05-20&date_limite_retour=2024-06-20"
- curl.exe -X DELETE "http://localhost:8000/emprunts/3"
- curl.exe -X DELETE "http://localhost:8000/emprunts/77"
