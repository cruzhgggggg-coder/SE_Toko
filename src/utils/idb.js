export const initDB = () => {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('TokoSumberMakmurDB', 1);

    request.onerror = (event) => {
      console.error('IndexedDB error:', event.target.error);
      reject(event.target.error);
    };

    request.onsuccess = (event) => {
      resolve(event.target.result);
    };

    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains('offline_transactions')) {
        db.createObjectStore('offline_transactions', { keyPath: 'id', autoIncrement: true });
      }
    };
  });
};

export const saveOfflineTransaction = async (payload) => {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['offline_transactions'], 'readwrite');
    const store = transaction.objectStore('offline_transactions');
    const request = store.add({ ...payload, createdAt: new Date().toISOString() });

    request.onsuccess = () => resolve(request.result);
    request.onerror = (e) => reject(e.target.error);
  });
};

export const getOfflineTransactions = async () => {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['offline_transactions'], 'readonly');
    const store = transaction.objectStore('offline_transactions');
    const request = store.getAll();

    request.onsuccess = () => resolve(request.result);
    request.onerror = (e) => reject(e.target.error);
  });
};

export const clearOfflineTransaction = async (id) => {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['offline_transactions'], 'readwrite');
    const store = transaction.objectStore('offline_transactions');
    const request = store.delete(id);

    request.onsuccess = () => resolve();
    request.onerror = (e) => reject(e.target.error);
  });
};
