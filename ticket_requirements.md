Workflow requirements
Use AI coding tools
- Preferably cursor for easier chat exports.
- You are free to use any custom rules/instructions prepared beforehand but please
include them in git
Use git to track progress of the project.
Delivery requirements
Host the project so that it’s publicly accessible (e.g. via upsun.com)
Submit artefacts of the project to the talent acquisition partner:
- Export of all AI chats used in creation of the project.
- Bundle of the git repository.
- List of optional requirements that you decided to implement.
Task requirements
Implement a website that matches the design in the screenshot and functionalities associated
with it.
We recommend focusing on the core requirements first and working on optional ones later on.
We recommend implementing at least 3 optional requirements.
You will not be penalised for skipping other optional requirements.
Design elements seen in the screenshot for optional requirements should still be implemented
(e.g. heart icon for favourites) but can be left non-functional if you decide not to complete the
logic behind it.
Optionally: implement a details page showcasing the information about the game.
Optionally: Implement login
Optionally: Implement add to cart and cart overview popup on cart icon hover.
Optionally: Implement favourites.
Optionally: Implement language switch with 3 languages.
Optionally: Implement currency conversion for 3 currencies.
Create a game catalogue with at least 100 games in it using igdb.com. Any missing data for the
games can be generated.
Backend must be written in php or go.
Frontend must be written in typescript.
There are no limitations for frameworks, databases, runtimes.
Game catalogue should be stored in some sort of database.
Expose the following functionalities:
- Paginated list of all available games
- User facing endpoint: /list
- JSON API endpoint: /api/list

- Paginated search across all games
- User facing endpoint: /list?search=<gamename>
- JSON API endpoint: /list?search=<gamename>
Implement fuzzy search (e.g. ifa, ffa should match fifa)
Optionally: implement synonym search (e.g. GTA 5 and GTA V).
Implement autocomplete for the search bar.
Optionally: implement fuzzy autocomplete (e.g. fffa -> fifa).
Optionally: implement synonym autocomplete (e.g. GTA5 -> GTA V).


