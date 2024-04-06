<?php

namespace App\Updates\Commands;

use CURLFile;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class SecretImage extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private', 'group', 'supergroup']);
        
        //$photo = new CURLFile(storage_path('verdant.jpg'),'image/jpg','testpic'); // uncomment and use if the upper procedural method is not working.

        // Cria uma imagem com largura e altura específicas
        $largura = 510;
        $altura = 310;
        $imagem = imagecreatetruecolor($largura, $altura);

        $card = imagecreate(640, 140);
        $cor_cartao = imagecolorallocate($card, 100, 0, 0);
        $cor_fundo = imagecolorallocate($card, 200, 200, 200);
        imagefilledrectangle($card, 10, 95, 180, 100, $cor_fundo);
        $card2 = imagecreate(190, 140);
        $card3 = imagecreate(190, 140);
        imagecopy($card2, $card, 0, 0, 0, 0, 190, 140);
        imagecopy($card3, $card, 0, 0, 0, 0, 190, 140);
        imagefttext($card, 20, 0, 10, 130, $cor_fundo, storage_path('Cat Comic.ttf'), date('d/m/y'));
        imagefttext($card, 20, 0, 10, 25, $cor_fundo, storage_path('Cat Comic.ttf'), '1');
        imagefttext($card2, 25, 0, 10, 130, $cor_fundo, storage_path('Cat Comic.ttf'), $this->getFirstName());
        imagefttext($card2, 20, 0, 10, 25, $cor_fundo, storage_path('Cat Comic.ttf'), '2');
        imagefttext($card3, 20, 0, 10, 130, $cor_fundo, storage_path('Cat Comic.ttf'), date('H:i:s'));
        imagefttext($card3, 20, 0, 10, 25, $cor_fundo, storage_path('Cat Comic.ttf'), '3');

        // Define a cor de fundo (branco)
        $cor_fundo = imagecolorallocate($imagem, 200, 200, 200);

        // Define uma cor para o texto (preto)
        $cor_texto = imagecolorallocate($imagem, 0, 0, 0);
        $cor_cartao = imagecolorallocate($imagem, 100, 0, 0);

        imagecopy($imagem, $card, 10, 10, 0, 0, 190, 140);
        imagecopy($imagem, $card2, 210, 10, 0, 0, 190, 140);
        imagecopy($imagem, $card3, 410, 10, 0, 0, 190, 140);

        imagefilledrectangle($imagem, 10, 160, 200, 300, $cor_cartao);
        imagefilledrectangle($imagem, 210, 160, 400, 300, $cor_cartao);
        imagefilledrectangle($imagem, 410, 160, 600, 300, $cor_cartao);

        //$gato = imagecreatefromjpeg(storage_path('endeota.jpg'));
        //imagecopy($imagem, $gato, 300, 300, 0, 0, 640, 640);

        $photos = $this->bot->getUserProfilePhotos($this->getUserId(), 0, 1);
        //$photo = $this->bot->getFile($photos->getPhotos()[0][2]->getFileId());
        //print_r($photo);die;
        //file_put_contents(storage_path('teste.jpg'), $photo);
        //$gato = imagecreatefromjpeg("https://api.telegram.org/file/bot".env('TG_TOKEN')."/".$photo->getFilePath());
        //imagecopyresampled($imagem, $gato, 300, 300, 0, 0, 640, 640, 640, 640);

        // Salva a imagem como um arquivo temporário
        $nome_arquivo = tempnam(sys_get_temp_dir(), 'imagem_');
        imagepng($imagem, $nome_arquivo);

        // Libera a memória da imagem
        imagedestroy($imagem);

        $photo = new CURLFile($nome_arquivo,'image/jpg','testpic'); // uncomment and use if the upper procedural method is not working.

        //$this->sendMessage('pong');
        $keyboard = new InlineKeyboardMarkup([
            [
                [
                    'text' => 'Escolher carta',
                    'switch_inline_query_current_chat' => 'escolher '
                ]
            ]
        ]);
        $this->bot->sendPhoto($this->getChatId(), $photo, 'imagem secreta', null, $keyboard);

        // Remove o arquivo temporário
        unlink($nome_arquivo);
    }

}