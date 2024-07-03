<?php

namespace App\Updates\Commands;

class Privacy extends Command {

    public function run() {
        if($this->isChatType('private')) {
            $text = "<b>Privacy Policy for Termogram</b>\n\nYour privacy is important to us. This Privacy Policy describes how we collect, use, and protect your personal information when using the Termogram bot.\n\n<b>1. Information We Collect</b>\n\nTermogram collects the following user information:\n- Username\n- First and last name\n- Language\n- User ID\n- Word packs created by users\n- Game statistics, including points, wins, losses, and words attempted\n\nThis information is collected through messages received in groups and private chats with the bot.\n\n<b>2. Use of Information</b>\n\nThe information collected is used for the following purposes:\n- To ensure the game always displays the user's updated name\n- To adjust the game's language according to the user's language\n- To use the words provided by users as words for the game\n- To differentiate users from each other during games using the user ID\n- To maintain statistics of all games played, including points, wins, losses, and words attempted\n\n<b>3. Protection of Information</b>\n\nWe value the security of your information and implement appropriate measures to protect it against unauthorized access, alteration, disclosure, or destruction. However, it is important to remember that no method of transmission over the internet or electronic storage is 100% secure.\n\n<b>4. Sharing of Information</b>\n\nWe do not share your personal information with third parties, except as necessary to comply with the law, regulation, or legal request.\n\n<b>5. Your Choices</b>\n\nYou have the right to access, correct, or delete your personal information at any time. To do so, please contact us through the comments section of the official developer channel at t.me/KianDev.\n\n<b>6. Changes to the Privacy Policy</b>\n\nWe may update this Privacy Policy from time to time. Any changes will be notified through the bot or other appropriate means. The updated policy will be posted on this page, and the revision date will be indicated at the top of the page. This policy was last updated on July 3, 2024. We encourage you to review our Privacy Policy periodically to stay informed about how we are protecting your information.\n\n<b>7. Contact</b>\n\nIf you have any questions or concerns about this Privacy Policy, please contact us via the comments section of the official developer channel at t.me/KianDev.";
        } else {
            $text = "/privacy is only available on the bot's DM";
        }
        
        $this->sendMessage($text, 'HTML');
    }

}