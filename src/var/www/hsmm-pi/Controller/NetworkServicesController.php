<?php
class NetworkServicesController extends AppController {
	public $helpers = array('Html', 'Form', 'Session');
	public $components = array('RequestHandler', 'Session');

	public function index() {
		$this->set('services', $this->NetworkService->find('all'));
		$this->load_node_attributes();
	}

	public function delete($id = null) {
		$this->NetworkService->id = $id;

		if (!$this->NetworkService->exists()) {
			throw new NotFoundException(__('Invalid service key'), 'default', array('class' => 'alert alert-error'));
		}

		if ($this->NetworkService->delete()) {
			$network_setting = $this->get_network_settings();
			$network_services = $this->get_network_services();
			$location = $this->get_location();

			$this->render_olsrd_config($network_setting, $network_services, $location);
			$this->render_rclocal_config($network_setting, $network_services);

			$this->Session->setFlash('The service has been deleted, and will take effect on the next reboot: <a href="#rebootModal" data-toggle="modal" class="btn btn-primary">Reboot</a>',
				'default', array('class' => 'alert alert-success'));
			$this->redirect(array('action' => 'index'));
		}
	}

	public function add() {
		if ($this->request->is('post')) {

			$this->NetworkService->create();
			if ($this->NetworkService->save($this->request->data)) {
				// retrieve other network settings
				$network_setting = $this->get_network_settings();
				$network_services = $this->get_network_services();
				$location = $this->get_location();

				$this->render_olsrd_config($network_setting, $network_services, $location);
				$this->render_rclocal_config($network_setting, $network_services);

				$this->Session->setFlash('The service has been added, and will take effect on the next reboot: <a href="#rebootModal" data-toggle="modal" class="btn btn-primary">Reboot</a>',
					'default', array('class' => 'alert alert-success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('Unable to add your service.', 'default', array('class' => 'alert alert-error'));
			}
		}
	}
}
?>

