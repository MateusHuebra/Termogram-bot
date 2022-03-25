## Termogram

**[Termogram](https://t.me/TermogramBot)** é um jogo inspirando em [Wordle](https://www.nytimes.com/games/wordle/index.html), onde você tem 6 tentativas de descobrir a palavra do dia. O seu grande diferencial é poder ser jogado diretamente no seu Telegram, sem ter que acessar diariamente o jogo por um navegador (o que pode ser um pouco chato). Basta deixas as notificações ativadas e esperar um novo desafio diário chegar em forma de mensagem.

## Tecnologias

Programado em **PHP 7**, utiliza o **Framework [Laravel 8](https://laravel.com/docs)** e **banco de dados MySQL**. E, apesar de inspirado em jogos já existentes, o código do Termogram foi feito do zero pensando na melhor forma de adequá-lo ao ambiente do Telegram. A comunicação com a API do Telegram foi feita utilizando a **biblioteca [telegram-bot/api](https://packagist.org/packages/telegram-bot/api)**, porém com diversas classes autorais para ajudar no direcionamento dos diferentes tipos de "updates" que o Telegram envia.

### Créditos

O dicionário de palavras da versão atual é o mesmo utilizado pelo [Xingo](https://github.com/jvdutra/xingo), por [jvdutra](https://github.com/jvdutra).
