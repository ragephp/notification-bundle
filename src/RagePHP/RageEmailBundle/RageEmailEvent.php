<?php
namespace RagePHP\RageEmailBundle;

final class RageEmailEvent
{
    /**
     * @var string
     */
    const BEFORE_RENDER_HTML = 'rage_email.before_render_html';

    /**
     * @var string
     */
    const AFTER_RENDER_HTML = 'rage_email.after_render_html';

    /**
     * @var string
     */
    const BEFORE_SEND = 'rage_email.before_send';

    /**
     * @var string
     */
    const AFTER_SEND = 'rage_email.after_send';
}