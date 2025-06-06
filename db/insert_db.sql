INSERT INTO client (nom, prenom, email, tel, date_inscription, date_naissance, adresse) VALUES
                                                                                            ('Durand', 'Alice', 'alice.durand@example.com', '0601020304', '2023-01-15', '1990-06-25', '12 rue des Lilas, Paris'),
                                                                                            ('Martin', 'Bob', 'bob.martin@example.com', '0604050607', '2023-02-20', '1985-03-10', '34 avenue Victor Hugo, Lyon');

INSERT INTO genre (nom) VALUES
                            ('Fantasy'),
                            ('Aventure'),
                            ('Science-fiction'),
                            ('Jeunesse');

INSERT INTO auteur (nom, prenom, date_naissance, date_mort) VALUES
                                                                ('Tolkien', 'J.R.R.', '1892-01-03', '1973-09-02'),
                                                                ('Rowling', 'J.K.', '1965-07-31', NULL);

INSERT INTO livre (titre, isbn, date_parution, genre_id) VALUES
                                                             ('Le Seigneur des Anneaux', '9782266232999', '1964-07-29', 1),
                                                             ('Harry Potter et la Coupe de Feu', '9782070585205', '2000-11-03', 4);

INSERT INTO livre_auteur (livre_id, auteur_id) VALUES
                                                   (1, 1),
                                                   (2, 2);

INSERT INTO emprunt (client_id, livre_id, date_emprunt, date_limite_retour, date_retour) VALUES
                                                                                             (1, 1, '2024-05-01', '2024-05-31', NULL),
                                                                                             (2, 2, '2024-04-20', '2024-05-20', '2024-05-10');
