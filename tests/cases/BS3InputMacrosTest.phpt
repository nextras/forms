<?php

/** @testcase */

namespace NextrasTests\Forms;

use Nette;
use Nette\Forms\Form;
use Nextras\Forms\Bridges\Latte\Macros\BS3InputMacros;
use Nextras\Forms\Rendering\Bs3FormRenderer;
use Tester\Assert;
use Tester\TestCase;
use Latte;
use Latte\Runtime\FilterExecutor;

require_once __DIR__ . '/../bootstrap.php';

class BS3InputMactrosTest extends TestCase
{
	/**
	 * @var Latte\Engine
	 */
	private $latte;
	/**
	 * @var string
	 */
	private $templateClassName;
	/**
	 * @var string
	 */
	private $resultTemplateFile;

	public function setUp()
	{
		$this->latte = new Latte\Engine;
		$this->latte->setLoader(new Latte\Loaders\StringLoader);
		BS3InputMacros::install($this->latte->getCompiler());

		@mkdir(TEMP_DIR);
		$this->resultTemplateFile = implode(DIRECTORY_SEPARATOR, [TEMP_DIR, 'BS3InputMactrosTest.' . __FUNCTION__ . '.php']);

		$codeToTest = '{input submit}';
		$this->templateClassName = $this->latte->getTemplateClass($codeToTest);
		file_put_contents($this->resultTemplateFile, $this->latte->compile($codeToTest));
	}

	public function testRawFormSubmit()
	{
		$form = new Form;
		$form->addSubmit('submit');

		Assert::same(
			'<input type="submit" name="_submit">',
			$this->evalTemplate($form, $this->latte, $this->templateClassName, $this->resultTemplateFile)
		);
	}

	public function testBs3FormRendererFormSubmit()
	{
		$form = new Form;
		$form->setRenderer(new Bs3FormRenderer);
		$formSubmit = $form->addSubmit('submit');

		Assert::throws(function () use ($form) {
			$this->evalTemplate($form, $this->latte, $this->templateClassName, $this->resultTemplateFile);
		}, Nette\InvalidArgumentException::class);

		$formSubmit->caption = 'Submit label';

		Assert::same(
			'<button type="submit" name="_submit" value="Submit label" class="btn">Submit label</button>',
			$this->evalTemplate($form, $this->latte, $this->templateClassName, $this->resultTemplateFile)
		);
	}

	private function evalTemplate(Form $form, Latte\Engine $latte, $templateClassName, $resultFile)
	{
		include_once $resultFile;

		/** @var Latte\Runtime\Template $tamplate */
		$tamplate = new $templateClassName($latte, [
			'form' => $form,
		], new FilterExecutor, [], __FUNCTION__);

		ob_start();
		$tamplate->main();

		return ob_get_clean();
	}
}

$test = new BS3InputMactrosTest();
$test->run();
