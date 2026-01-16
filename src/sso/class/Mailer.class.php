<?php

namespace SSO;

use PHPMailer\PHPMailer;

class MailerModelConsistencyException 				extends \Exception {}
class MailerConfirmationCodeConsistencyException 	extends \Exception {}
class MailerTokenConsistencyException				extends	\Exception {}
class MailerTokenLinkConsistencyException 			extends \Exception {}

final
class Mailer
{
	private
	const 	CSS_W_ALL 				= ['style', 'width: 100%'],
			LOGO_PATH 				= '%1$s/../tmp/%2$s.png',
			RAND_LENGTH 			= 10,
			SYMBOL_LOCATION         = '&#x2680;',
			SYMBOL_PHONE            = '&#9743;',
			SYMBOL_WEBSITE          = '&#9755;';

	public
	const 	MODEL_LOST_PASSWORD 	= 'lostPassword';

	protected
	const 	AUTHORIZED_MODELS 		= [ self::MODEL_LOST_PASSWORD ];

    private	$dirname,
			$generated 				= false,
			$html,
			$mailer,
			$model,
			$view;

	/**
	 * @param 	?int	$debug 	1,2,3 allowed
	 */
    public
    function __construct ( int $debug = 0 )
    {
		$this->dirname 				= dirname(__FILE__);
        $this->mailer               = new PHPMailer\PHPMailer;
        $this->mailer->isSMTP();
        $this->mailer->SMTPDebug    = $debug;
        $this->mailer->Host         = ( $system = SsoSystem::Instance() )->{ $system::SMTP_SERVER };
        $this->mailer->Port         = $system->{ $system::SMTP_PORT };
        $this->mailer->SMTPAuth     = true;
        $this->mailer->Username     = ( $user = $system->{ $system::SMTP_USER } );
        $this->mailer->Password     = $system->{ $system::SMTP_PWD };
		$this->mailer->CharSet 		= 'utf-8';
        $this->mailer->SMTPSecure 	= 'tls';
		$this->mailer->Encoding 	= 'base64';
        $this->mailer->Subject      = sprintf('%1$s %2$s', 'Une communication de :', $appName = $system->{ $system::SMTP_NAME });

		$this->mailer->setFrom($user, $appName);
		$this->mailer->addReplyTo($user, $appName);

		$this->view = new \HtmlGenerator;

		$this->logo();
    }

	public
	function __destruct ()
	{
		$this->logo(true);
	}

	/**
	 * attaches a file to mail
	 *
	 * @param 	string 	$pathToFile
	 *
	 * @return 	bool
	 */
	public
	function addFile ( string $pathToFile ) : bool
	{
		return ! file_exists($pathToFile) ? false : $this->mailer->addAttachment($pathToFile);
	}

	/**
	 * creates HTML content based on $this->buildModel()`
	 *
	 * @return 	\DOMElement
	 */
	protected
	function buildHtmlView () : \DOMElement
	{
		$this->view
			->__add( $this->buildModel(), $main = $this->view->__div() )
			->__att($main, ...self::CSS_W_ALL);

		return $main;
	}

	/**
	 * creates HTML content based on model
	 *
	 * @return 	\DOMElement
	 *
	 * @throws 	MailerModelConsistencyException
	 * @throws 	MailerConfirmationCodeConsistencyException
	 * @throws 	MailerTokenConsistencyException
	 * @throws 	MailerTokenLinkConsistencyException
	 */
	protected
	function buildModel () : \DOMElement
	{
		switch ( $this->model )
		{
			case self::MODEL_LOST_PASSWORD:
				$this->view
					->__add(
						[
							$hello 	= $this->view->__div(
								sprintf(
									trim('Bonjour %1$s %2$s,'),
									( $caps = fn ( string $str ) : string => ucwords( mb_strtolower($str) ) )(
										( $vo = ( $user = User::Instance() )->getVirtual() )->{ $user->f_firstName }
									),
									$caps( $vo->{ $user->f_lastName } )
								)
							),
							$why 	= $this->view->__div('Vous semblez avoir oublié votre mot de passe.'),
							$how 	= $this->view->__div('Merci de suivre le lien temporaire suivant sans tarder, pour le renouveler.'),
							$link 	= $this->view->__div(),
							$safe 	= $this->view->__div('Si cette demande n\'émanait pas de vous, contactez au plus vite le Bureau d\'Etudes - Informatique.'),
							$best 	= $this->view->__div('Cordialement,')
						],
						$txtMsg = $this->view->__div()
					)
					->__add(
						[
							$pic 	= $this->view->__a(SsoView::EMO_RECYCLE),
							$hint 	= $this->view->__a( sprintf('%1$s Je clique pour renouveler mon mot de passe !', SsoView::EMO_FINGER_LEFT) )
						],
						$link
					)
					->__att( $hello, ...( $margin = [$style = 'style', 'margin: .4em'] ) )
					->__att($why, ...$margin)
					->__att($how, ...$margin)
					->__att($safe, ...$margin)
					->__att($best, ...$margin)
					->__att($link, $style, 'display: flex; flex-wrap: nowrap; justify-content: flex-start; align-items: center')
					->__att(
						$pic,
						...( $linking = [
							'href',
							sprintf(
								'%1$s/%2$s',
								SsoView::createLink(SsoView::ROAD_NEW_PWD),
								Token::Instance()->getGenerated()
							)
						] )
					)
					->__att($hint, ...$linking)
					->__att($pic, $style, 'text-decoration: none; margin-left: 2em; font-size: 2.8em')
					->__att($hint, $style, 'font-size: 1.1em; font-style: oblique; margin-left: 1.1em; line-height: 3.8em');

				break;

			default: throw new MailerModelConsistencyException;
		}

		$this->view
			->__add( $txtMsg, $msg = $this->view->__div() )
			->__att($msg, ...self::CSS_W_ALL);

		return $msg;
	}

	/**
	 * creates mail's frame, using `$this->buildHtmlView()`
	 */
	protected
	function createFrameContent () : void
	{
		$this->view
			->__add(
				[
					$head = $this->view->__head(),
					$body = $this->view->__body()
				],
				$this->html = $this->view->__html()
			)
			->__add(
				$this->view->__title( ( $appName = htmlentities(( $system = SsoSystem::Instance() )->{ $system::SMTP_NAME }) ) ),
				$head
			)
			->__add(
				[
					$this->buildHtmlView(),
					$signature 	= $this->view->__div(),
					$sign_place	= $this->view->__div()
				],
				$body
			)
			->__add(
				[
					$sign_img = $this->view->__img(),
					$sign_txt = $this->view->__div()
				],
				$signature
			)
			->__add(
				[
					$sign_title	= $this->view->__div($appName),
					$sign_phone	= $this->view->__div(),
					$sign_link 	= $this->view->__div()
				],
				$sign_txt
			)
			->__add(
				$this->createIconedLinkRow(
					self::SYMBOL_PHONE,
					sprintf(
						'tel:%1$s',
						str_replace(' ', '', $system->{ $system::COMPANY_PHONE })
					),
					$system->{ $system::COMPANY_PHONE },
					1.8
				),
				$sign_phone
			)
			->__add(
				$this->createIconedLinkRow(
					self::SYMBOL_WEBSITE,
					$system->{ $system::COMPANY_WEBSITE },
					str_replace('http://', '', $system->{ $system::COMPANY_WEBSITE })
				),
				$sign_link
			)
			->__add(
				$this->createIconedLinkRow(
					self::SYMBOL_LOCATION,
					$system->{ $system::COMPANY_GOOGLE_URL },
					$system->{ $system::COMPANY_LOCATION }
				),
				$sign_place
			)
			->__att($this->html, 'lang', 'fr')
			->__att( $body, $style = 'style', sprintf('color: %1$s; font-family: cursive', $system->{ $system::COMPANY_COLOR }) )
			->__att($signature, $style, 'width: 100%; display: flex; flex-wrap: nowrap; justify-content: flex-start; align-items: start')
			->__att($sign_img, $style, 'width: 10%; min-width: 210px')
			->__att($sign_img, 'src', sprintf('cid:%1$s', $tmpFileName = basename($this->generated)))
			->__att($sign_txt, $style, 'width: 85%; margin-left: 5%; white-space: nowrap')
			->__att($sign_title, $style, 'font-weight: bold; font-size: 1.7em; margin-top: 0.7em')
			->__att($sign_phone , $style, $margedTop = 'margin-top: 0.5em')
			->__att($sign_link , $style, $margedTop)
			->__att($sign_place , $style, 'width: 95%; margin-left: 5%');

		$this->mailer->addEmbeddedImage( $this->generated, $tmpFileName );
	}

	/**
	 * creates a link row
	 *
	 * @param	string 	$iconHexOrHtmlCode
	 * @param	string 	$to
	 * @param	string 	$text
	 * @param	?float 	$iconSize
	 *
	 * @return 	\DOMElement
	 */
	protected
	function createIconedLinkRow ( string $iconHexOrHtmlCode, string $to, string $text, float $iconSize = 2.1 ) : \DOMElement
	{
		$this->view
			->__add(
				[
					$link 		= $this->view->__a(),
					$spanText 	= $this->view->__span($text)
				],
				$container = $this->view->__div()
			)
			->__add( $spanIcon = $this->view->__span($iconHexOrHtmlCode), $link )
			->__att($container, $style = 'style', 'font-size:0.9em; font-weight:normal')
			->__att($link, 'target', '_blank')
			->__att( $link, $style, sprintf('color:%1$s; text-decoration:none', $colored = ( $system = SsoSystem::Instance() )->{ $system::COMPANY_COLOR } ) )
			->__att($link, 'href', $to)
			->__att( $spanIcon, $style, sprintf('font-size:%1$sem; color:%2$s', $iconSize, $colored) )
			->__att( $spanText, $style, sprintf('color:%1$s; margin-left: 5px', $colored) );

		return $container;
	}

	/**
	 * determines if given email address seems valid
	 *
	 * @param 	string 	$email
	 *
	 * @return 	bool
	 */
	public static
	function isValid ( string $email ) : bool
	{
		return \PHPMailer\PHPMailer\PHPMailer::validateAddress($email);
	}

	/**
	 * creates a temporary logo for mail use
	 *
	 * @param 	?bool 	$destroy
	 *
	 * @return 	string|false
	 */
	private
	function logo ( bool $destroy = false )
	{
		do
		{
			if ( $destroy ) break;

			$path = sprintf(self::LOGO_PATH, $this->dirname, bin2hex( random_bytes(self::RAND_LENGTH) ));
			$img = imagecreatefromstring( base64_decode( SsoSystem::Instance()->{ SsoSystem::COMPANY_LOGO_B64_PNG } ) );

			if ( ! $img ) break;

			$this->generated = imagepng($img, $path) ? $path : false;
		}
		while ( 0 );

		if ( $destroy && $this->generated ) $this->generated = ! unlink($this->generated);

		return $this->generated;
	}

	/**
	 * reads last error from `$this->mailer`
	 *
	 * @return 	string
	 */
	public
	function readError () : string
	{
		return $this->mailer->ErrorInfo;
	}

	/**
	 * renders HTML message's view
	 *
	 * @return 	string
	 */
	protected
	function render () : string
	{
		return html_entity_decode( $this->view->doc->saveHTML($this->html) );
	}

	/**
	 * sends mail
     *
	 * @param   string  $what   model
	 * @param 	string	$to
	 *
	 * @return 	bool
	 */
	public
	function send ( string $what, string $to ) : bool
	{
		do
		{
            $this->setModel($what);

			if ( ! $this->mailer->addAddress($to) ) break;
			if ( is_null($this->model) ) 			break;

			try {
				$this->createFrameContent();
				$this->mailer->msgHTML( $this->render() );
			}
			catch ( \Throwable $t ) {
				error_log( sprintf('mail sending error: %1$s', $errored = $t->getMessage()));
				break;
			}

			$success = $this->mailer->send();
		}
		while ( 0 );

		return $success ?? false;
	}

	/**
	 * sets a new carbon copy
	 *
	 * @param 	string 	$email
	 *
	 * @return 	bool
	 */
	public
	function setCarbonCopy ( string $email ) : bool
	{
		return $this->mailer->addCC($email);
	}

	/**
	 * sets debug mode on for client / server exchanges
	 *
	 * @return 	Mailer
	 */
	public
	function setDebugModeOn () // : static
	{
		$this->mailer->SMTPDebug = PHPMailer\SMTP::DEBUG_SERVER;

		return $this;
	}

	/**
	 * sets message's content
	 *
	 * @param 	\DOMDocument 	$content
	 *
	 * @deprecated
	 */
	public
	function setMesssage ( \DOMDocument $content ) : void
	{
		$this->mailer->msgHTML(html_entity_decode( $content->saveHTML() ));
	}

	/**
	 * sets mail model
	 *
	 * @param 	string 		$modelName
	 */
	private
	function setModel ( string $modelName ) : void
	{
		$this->model = ! in_array($modelName, static::AUTHORIZED_MODELS) ? null : $modelName;
	}
}
