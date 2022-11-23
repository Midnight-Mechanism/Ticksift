import Modal from 'react-modal';

import './styles.css';

export default function FormModal({
  title = '',
  body,
  isOpen,
  closeModal,
}: {
  title?: string;
  body: any;
  isOpen: boolean;
  closeModal: any;
}) {
  Modal.setAppElement('#app');

  return (
    <Modal className="modal" overlayClassName="modal-overlay" isOpen={isOpen} onRequestClose={closeModal}>
      <div className="flex justify-between items-start pb-3 border-b dark:border-gray-600">
        <h3 className="text-2xl font-semibold text-gray-900 dark:text-white">{title}</h3>
        <button className="text-2xl" onClick={closeModal}>
          &times;
        </button>
      </div>
      <div className="pt-3">{body}</div>
    </Modal>
  );
}
