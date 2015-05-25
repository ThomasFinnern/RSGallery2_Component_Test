<?php
defined('_JEXEC') or die;

// Include the JLog class.
jimport('joomla.log.log');

// identify active file
JLog::add('==> base.controller.php ');

// import Joomla controller library
jimport('joomla.application.component.controller');

/**
 * Class Rsgallery2Controller
 */
class Rsgallery2Controller extends JControllerLegacy
{
    // protected $default_view = 'rsg2s';
    
	/**
	 * display task
	 * @param bool $cachable
	 * @param bool $urlparams
	 * @return $this
	 */
	public function display($cachable = false, $urlparams = false)
	{

//		require_once JPATH_COMPONENT.'/helpers/rsg2.php';

/*		$view   = $this->input->get('view', 'rsg2');
//		JLog::add('  base.controller.view: ', json_encode($view));
		
//		$layout = $this->input->get('layout', 'default');
		JLog::add('  base.controller.layout: ', json_encode($layout));
		
		$id     = $this->input->getInt('id');
		JLog::add('  base.controller.id: ', json_encode($id));
		
		$task = $this->input->get('task');
		JLog::add('  base.controller.task: ', json_encode($task));
*/
		
/* ToDo:: Activate following: book extension entwickeln  page 208
		if ($view == 'rsg2' && $layout == 'edit' && !$this->checkEditId('com_rsg2.edit.rsgallery2', $id))
		{
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_rsg2&view=rsgallery2s', false));

			return false;
		}
*/
		parent::display($cachable);

		return $this;
	}

}
