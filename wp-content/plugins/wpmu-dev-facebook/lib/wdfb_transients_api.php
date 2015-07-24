<?php

/**
 * Transient buffering hub class.
 */
abstract class Wdfb_TransientBuffer {

	const TRANSIENT_TIMEOUT = 21600; // 6 hours timeout by default;

	abstract public function get_for ($id, $limit=false);

	protected function get_transient_name ($type, $id) {
		return "wdfb-{$type}-{$id}";
	}

	protected function fetch ($transient) {
		return get_transient($transient);
	}

	protected function store ($transient, $value) {
		return set_transient($transient, $value, apply_filters('wdfb-transients-buffer_timeout', self::TRANSIENT_TIMEOUT));
	}
}
/**
 * Facebook Events concrete implementation.
 */
class Wdfb_EventsBuffer extends Wdfb_TransientBuffer {

	public function get_for ($fbid, $limit=false) {
		if (!$fbid) return false;
		$transient = $this->get_transient_name('events', $fbid);

		$result = $this->fetch($transient);
		if ($result) return $result;

		$model = new Wdfb_Model;
		$events = $model->get_events_for($fbid, $limit);
		if (!$events) return false;

		$this->store($transient, $events['data']);
		return $events['data'];
	}
}

/**
 * Facebook Album photos concrete implementation.
 */
class Wdfb_AlbumPhotosBuffer extends Wdfb_TransientBuffer {

	public function get_for ($album_id, $limit=false) {
		if (!$album_id) return false;
		$transient = $this->get_transient_name('album_photos', $album_id);

		$result = $this->fetch($transient);
		if ($result) return $result;

		$model = new Wdfb_Model;
		$photos = $model->get_album_photos($album_id, $limit);
		if (!$photos) return false;

//		$this->store($transient, $photos['data']);
		return $photos['data'];
	}
}