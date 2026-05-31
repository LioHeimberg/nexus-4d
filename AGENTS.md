# Nexus 4D

Nexus 4D is a web tool mainly for the **JugendTeam** Members. they can see their own stats like the review of their customers at the rümli bar, or the actuel events where Rokja:4D is helping or where they have a booth at an event etc... the Adults Team, the 'Bosses' of the youngsters, can write and publish events etc with an adminlike tool.

## Accounts
- there is one **ADMIN** account wich is the first one to create if there isn't one in the database.
- - admin account has full contoll of all posts, accounts and all...
- one account for the adults, not three, just one for the entire team. its called '**bosses account**' this one is the first account that the admin has to create if he has first logged in
- - the boss account can create, manage and remove JugendTeam Member Accounts and can write and publish posts and events. they also can see the stats of the JugendTeam Members.
- then the last layer: the **JugendTeam Member** Account. this one is mainly created by the boss account. The admin account can also manage them.
- - the JugendTeam Member Account can see their own reviews from the bosses and from the customers of the bars and events. they can see the posts of the bosses and can vote if they are gonna be there to help rokja or not at the event of the post.
- last: the **GUEST** Account.
- - this isn't really an account where you have to log in. you can select the jugendteam member who served you and maybe the event you were when he served you and then you can give a review with stars in 3 main point friendly etc. if they aren't at an Event but in a Rümli (this is a bar of the rokja) you can give also a review. the event selection is only for the data evaluation or the dashbords of the members.

## Structure and technologies
- we will have a database and a webserver. the webserver supports PHP.
- in the httpdocs folder there is the things put on the webserver. like front and backend etc
- then, in the main directory (where also AGENTS.md, README.md and opencode.json etc is) we wil have a docker compose and a folder initdb.d with the database for a developer environment.
- the intire project will only be coded in PHP, HTML, Javascript and CSS.
- login, authorisation, data requests etc all goes through /api
- every account has to login except the guests
- we **WONT DO MESSY THINGS like saving the session in the php session** etc (**save the session in the local storage please!**). ONLY PROFETIONAL WORK!
- no /includes folder, all php in /api
- we will use mariadb, phpmyadmin and apache for developer server
- /api, /assets etc, just everything that needs to be on the webserver is in the /httpdocs folder

## infos about Rokja:4D :

ROKJA:4D – Regionale Offene Kinder- und Jugendarbeit

Wünnewil-Flamatt | Schmitten | Ueberstorf

Seit August 2025 sind wir als ROKJA:4D unterwegs und organisieren die Jugendarbeit neu in vier Dörfern mit vier Jugendräumen.

Gemeinsam gestalten wir die regionale Jugendarbeit in den Jugendräumen und draussen im öffentlichen Raum. Wir führen Gespräche, organisieren Projekte und unterstützen Kinder & Jugendliche in ihren Ideen.

Unser Team

Kevin Zeh – Stellenleiter, weiterhin verantwortlich für die Jugendräume Wünnewil & Flamatt
Raphael Kaufmann – bisher Jugendarbeiter in Schmitten
Noë Scheidegger – seit August neu im Team der ROKJA:4D

## infos about JugendTeam:

Jugendteam
Jugendliche die sich engagieren
Jugendliche des Jugendteams tragen dazu bei, die Jugendarbeit – ROKJA :4D –  stark zu machen und etwas zu bewegen. Jugendliche der 7.-9. Klasse aus Wünnewil-Flamatt, Schmitten & Ueberstorf können sich allen Jugendräumen engagieren. Das Jugendteam ist von grosser Wichtigkeit und Pfeiler der Arbeit mit den Jugendlichen in der Region.

## color scheme
- the logo of Rokja:4D is colorful, 'R' is #2e63a8, 'O' is #30a3db, 'K' is #3eb074, 'J' is #ecd057, 'A' is #eb576b and ':4D' is black
- the webtool should be clean and modern
- also please add a Dark mode.
- primary and secondary color need to be determined.

## Current state
- nothing started yet, agents.md is written