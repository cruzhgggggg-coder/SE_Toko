<template>
  <div class="inventory-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Manajemen Stok</h1>
        <p class="page-subtitle">Pantau dan kelola inventaris barang secara real-time</p>
      </div>
      <button class="btn-add-product" @click="showAdd = true">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Produk Baru
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="inv-stats">
      <div class="inv-stat">
        <p class="inv-stat-label">TOTAL SKU AKTIF</p>
        <p class="inv-stat-val">{{ inventoryStore.stats.totalSKU }}</p>
        <span class="inv-stat-badge ok">Semua Normal</span>
      </div>
      <div class="inv-stat">
        <p class="inv-stat-label">STOK MENIPIS</p>
        <p class="inv-stat-val danger">{{ inventoryStore.stats.lowStock }}</p>
        <span class="inv-stat-badge warn">Perlu Restock</span>
      </div>
      <div class="inv-stat">
        <p class="inv-stat-label">STOK HABIS</p>
        <p class="inv-stat-val danger">{{ inventoryStore.stats.emptyStock }}</p>
        <span class="inv-stat-badge danger">Segera Isi</span>
      </div>
      <div class="inv-stat">
        <p class="inv-stat-label">NILAI TOTAL STOK</p>
        <p class="inv-stat-val sm">Rp {{ formatNum(inventoryStore.stats.totalValue) }}</p>
        <span class="inv-stat-badge ok">+5% bulan ini</span>
      </div>
    </div>

    <!-- Filter & Search -->
    <div class="toolbar">
      <div class="search-box">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="search" type="text" placeholder="Cari nama atau SKU barang..." />
      </div>
      <select v-model="filterCat" class="select-filter">
        <option value="">Semua Kategori</option>
        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
      </select>
      <select v-model="filterStock" class="select-filter">
        <option value="">Semua Stok</option>
        <option value="low">Stok Menipis</option>
        <option value="empty">Stok Habis</option>
        <option value="ok">Stok Aman</option>
      </select>
      <button class="btn-manage-cat" @click="showCategoryManager = true">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Kelola Kategori
      </button>
    </div>

    <!-- Product Table -->
    <div class="table-card">
      <div class="table-head-row">
        <span class="col-product">PRODUK</span>
        <span>SKU</span>
        <span>KATEGORI</span>
        <span class="text-center">STOK SAAT INI</span>
        <span>MIN. STOK</span>
        <span>HARGA BELI</span>
        <span>HARGA JUAL</span>
        <span>AKSI</span>
      </div>

      <div v-for="p in filteredProducts" :key="p.id" class="table-data-row">
        <div class="col-product product-info">
          <div class="product-icon">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#94A3B8" stroke-width="1.5">
              <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
            </svg>
          </div>
          <div>
            <p class="prod-name">{{ p.name }}</p>
            <p class="prod-unit">{{ p.unit }}</p>
          </div>
        </div>
        <span class="sku-text">{{ p.sku }}</span>
        <span class="cat-pill">{{ p.category }}</span>
        <div class="stock-cell">
          <span class="stock-num" :class="getStockClass(p.total_stock, p.min_stock)">{{ p.total_stock }}</span>
        </div>
        <span class="min-stock">{{ p.min_stock }}</span>
        <span class="price-text">Rp {{ formatNum(p.batches?.[0]?.buy_price || 0) }}</span>
        <span class="price-text sell">Rp {{ formatNum(p.batches?.[0]?.sell_price || 0) }}</span>
        <div class="action-btns">
          <button class="act-btn edit" @click="editProduct(p)" title="Edit">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button class="act-btn restock" @click="showRestock(p)" title="Restock">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <button class="act-btn delete" @click="deleteProduct(p)" title="Hapus Produk">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
          </button>
        </div>
      </div>

      <div class="table-footer">
        Menampilkan {{ filteredProducts.length }} dari {{ inventoryStore.products.length }} produk
      </div>
    </div>

    <!-- Toast Notification -->
    <ToastNotification
      v-if="toast.show"
      :message="toast.message"
      :type="toast.type"
      @hide="toast.show = false"
    />

    <!-- Add Product Modal -->
    <!-- Add Modal -->
    <div v-if="showAdd" class="modal-overlay">
      <div class="modal-box">
        <div class="modal-header">
          <h2 class="modal-title">Tambah Produk Baru</h2>
          <button class="close-btn" @click="showAdd = false">✕</button>
        </div>
        <div class="add-form">
          <div class="form-group">
            <label>Nama Produk</label>
            <input v-model="newProd.name" class="form-input" placeholder="Masukkan nama produk" />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>SKU</label>
              <input v-model="newProd.sku" class="form-input" placeholder="Otomatis jika kosong" />
            </div>
            <div class="form-group">
              <label>Kategori</label>
              <input 
                v-model="newProd.category" 
                class="form-input" 
                list="category-suggestions" 
                placeholder="Pilih atau ketik kategori..." 
              />
              <datalist id="category-suggestions">
                <option v-for="cat in categories" :key="cat" :value="cat" />
              </datalist>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Satuan</label>
              <input v-model="newProd.unit" class="form-input" placeholder="pcs, kg, sak, dll" />
            </div>
            <div class="form-group">
              <label>Minimal Stok</label>
              <input v-model.number="newProd.min_stock" class="form-input" type="number" min="0" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Harga Beli (Rp)</label>
              <input v-model.number="newProd.buy_price" class="form-input" type="number" min="0" />
            </div>
            <div class="form-group">
              <label>Harga Jual (Rp)</label>
              <input v-model.number="newProd.sell_price" class="form-input" type="number" min="0" />
            </div>
          </div>
          <div class="form-group">
            <label>Stok Awal <span style="color: var(--color-danger);">*</span></label>
            <input v-model.number="newProd.initial_stock" class="form-input" type="number" min="1" placeholder="Jumlah stok awal yang harus diisi" />
            <small style="color: var(--color-text-muted); font-size: 11px; margin-top: 2px;">Wajib diisi. Stok awal akan menjadi batch pertama produk ini.</small>
          </div>
          <div class="modal-actions">
            <button class="btn-cancel" @click="showAdd = false">Batal</button>
            <button class="btn-confirm" @click="saveProduct">Simpan Produk</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Edit Product Modal -->
    <div v-if="showEdit" class="modal-overlay">
      <div class="modal-box">
        <div class="modal-header">
          <h2 class="modal-title">Edit Produk: {{ editData.name }}</h2>
          <button class="close-btn" @click="showEdit = false">✕</button>
        </div>
        <div class="add-form">
          <div class="form-row">
            <div class="form-group">
              <label>Nama Produk</label>
              <input v-model="editData.name" class="form-input" />
            </div>
            <div class="form-group">
              <label>SKU</label>
              <input v-model="editData.sku" class="form-input" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Kategori</label>
              <input 
                v-model="editData.category" 
                class="form-input" 
                list="edit-category-suggestions" 
                placeholder="Pilih atau ketik kategori..." 
              />
              <datalist id="edit-category-suggestions">
                <option v-for="cat in categories" :key="cat" :value="cat" />
              </datalist>
            </div>
            <div class="form-group">
              <label>Satuan</label>
              <input v-model="editData.unit" class="form-input" />
            </div>
          </div>
          <div class="form-group" style="width: 50%; padding-right: 8px;">
            <label>Minimal Stok</label>
            <input v-model.number="editData.min_stock" class="form-input" type="number" min="0" />
          </div>
          <div class="modal-actions">
            <button class="btn-cancel" @click="showEdit = false">Batal</button>
            <button class="btn-confirm" @click="saveEdit">Simpan Perubahan</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Restock Modal -->
    <div v-if="showRestockModal" class="modal-overlay">
      <div class="modal-box">
        <div class="modal-header">
          <h2 class="modal-title">Restock: {{ selectedProduct?.name }}</h2>
          <button class="close-btn" @click="showRestockModal = false">✕</button>
        </div>
        <div class="add-form">
          <div class="form-row">
            <div class="form-group">
              <label>Nomor Batch</label>
              <input v-model="restockData.batch_number" class="form-input" />
            </div>
            <div class="form-group">
              <label>Jumlah Masuk</label>
              <input v-model.number="restockData.qty" class="form-input" type="number" min="1" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Harga Beli (Rp)</label>
              <input v-model.number="restockData.buy_price" class="form-input" type="number" min="0" />
            </div>
            <div class="form-group">
              <label>Harga Jual (Rp)</label>
              <input v-model.number="restockData.sell_price" class="form-input" type="number" min="0" />
            </div>
          </div>
          <div class="form-group">
            <label>Tanggal Kadaluarsa</label>
            <input v-model="restockData.expired_date" class="form-input" type="date" />
          </div>
          <div class="modal-actions">
            <button class="btn-cancel" @click="showRestockModal = false">Batal</button>
            <button class="btn-confirm" @click="handleRestock">Simpan Stok</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Manager Modal -->
    <div v-if="showCategoryManager" class="modal-overlay">
      <div class="modal-box" style="width: 500px;">
        <div class="modal-header">
          <h2 class="modal-title">Kelola Kategori</h2>
          <button class="close-btn" @click="showCategoryManager = false">✕</button>
        </div>
        <div class="category-manager-content">
          <!-- Add Category Form -->
          <div class="add-category-form" style="display: flex; gap: 8px; margin-bottom: 16px;">
            <input 
              v-model="newCategoryName" 
              class="form-input" 
              style="flex: 1;" 
              placeholder="Nama kategori baru..." 
              @keyup.enter="createCategory"
            />
            <button class="btn-confirm" style="padding: 10px 16px; flex: none;" @click="createCategory">Tambah</button>
          </div>

          <!-- Category List -->
          <div class="category-list" style="max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
            <div v-for="cat in categoriesList" :key="cat.id" class="category-item-row" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: var(--radius-md);">
              <span v-if="editingCategoryId !== cat.id" class="category-name-span" style="font-weight: 600; color: var(--color-text-primary);">{{ cat.name }}</span>
              <input 
                v-else 
                v-model="editingCategoryName" 
                class="form-input" 
                style="padding: 4px 8px; font-size: 13px;" 
                @keyup.enter="saveCategoryEdit(cat)"
              />

              <div class="category-action-btns" style="display: flex; gap: 6px;">
                <button 
                  v-if="editingCategoryId !== cat.id" 
                  class="act-btn edit" 
                  style="width: 26px; height: 26px;"
                  @click="startCategoryEdit(cat)"
                >
                  <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button 
                  v-else 
                  class="act-btn restock" 
                  style="width: 26px; height: 26px; background: #DCFCE7; color: #166534;"
                  @click="saveCategoryEdit(cat)"
                >
                  ✓
                </button>

                <button 
                  class="act-btn edit" 
                  style="width: 26px; height: 26px; color: var(--color-danger); border-color: var(--color-danger-light);"
                  @click="deleteCategory(cat.id)"
                >
                  ✕
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useInventoryStore } from '@/stores/inventory'
import axios from '@/plugins/axios'
import ToastNotification from '@/components/shared/ToastNotification.vue'

const inventoryStore = useInventoryStore()
const search = ref('')
const filterCat = ref('')
const filterStock = ref('')
const showAdd = ref(false)
const showEdit = ref(false)
const showRestockModal = ref(false)
const selectedProduct = ref(null)

const toast = ref({ show: false, message: '', type: 'success' })
function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
}

// Dynamic Category Management State
const showCategoryManager = ref(false)
const categoriesList = ref([])
const newCategoryName = ref('')
const editingCategoryId = ref(null)
const editingCategoryName = ref('')

const fetchCategories = async () => {
  try {
    const response = await axios.get('/categories')
    categoriesList.value = response.data
  } catch (err) {
    console.error('Error fetching categories:', err)
  }
}

const createCategory = async () => {
  if (!newCategoryName.value.trim()) return
  try {
    await axios.post('/categories', { name: newCategoryName.value.trim() })
    newCategoryName.value = ''
    await fetchCategories()
    showToast('Kategori baru berhasil ditambahkan!')
  } catch (err) {
    console.error(err)
    showToast(err.response?.data?.message || 'Gagal menambahkan kategori', 'error')
  }
}

const startCategoryEdit = (cat) => {
  editingCategoryId.value = cat.id
  editingCategoryName.value = cat.name
}

const saveCategoryEdit = async (cat) => {
  if (!editingCategoryName.value.trim()) return
  try {
    await axios.put(`/categories/${cat.id}`, { name: editingCategoryName.value.trim() })
    editingCategoryId.value = null
    await fetchCategories()
    showToast('Kategori berhasil diperbarui!')
  } catch (err) {
    console.error(err)
    showToast(err.response?.data?.message || 'Gagal memperbarui kategori', 'error')
  }
}

const deleteCategory = async (id) => {
  if (!confirm('Apakah Anda yakin ingin menghapus kategori ini?')) return
  try {
    await axios.delete(`/categories/${id}`)
    await fetchCategories()
    showToast('Kategori berhasil dihapus!')
  } catch (err) {
    console.error(err)
    showToast(err.response?.data?.message || 'Gagal menghapus kategori', 'error')
  }
}

const categories = computed(() => categoriesList.value.map(c => c.name))

const newProd = ref({ name: '', sku: '', category: '', unit: 'kg', min_stock: 5, buy_price: 0, sell_price: 0, initial_stock: 1 })
const editData = ref({ id: null, name: '', sku: '', category: '', unit: '', min_stock: 0 })
const restockData = ref({ batch_number: '', qty: 0, buy_price: 0, sell_price: 0, expired_date: '' })

onMounted(async () => {
  await inventoryStore.fetchProducts()
  await fetchCategories()
})

const filteredProducts = computed(() => {
  return inventoryStore.products.filter(p => {
    const matchSearch = !search.value || p.name.toLowerCase().includes(search.value.toLowerCase()) || (p.sku || '').toLowerCase().includes(search.value.toLowerCase())
    const matchCat = !filterCat.value || p.category === filterCat.value
    const matchStock = !filterStock.value ||
      (filterStock.value === 'empty' && p.total_stock === 0) ||
      (filterStock.value === 'low'   && p.total_stock > 0 && p.total_stock <= p.min_stock) ||
      (filterStock.value === 'ok'    && p.total_stock > p.min_stock)
    return matchSearch && matchCat && matchStock
  })
})

function getStockClass(stock, min) {
  if (stock === 0) return 'empty'
  if (stock <= min) return 'low'
  return 'ok'
}


function editProduct(p) {
  editData.value = {
    id: p.id,
    name: p.name,
    sku: p.sku,
    category: p.category,
    unit: p.unit,
    min_stock: p.min_stock
  }
  showEdit.value = true
}

async function saveEdit() {
  try {
    await axios.put(`/products/${editData.value.id}`, editData.value)
    await inventoryStore.fetchProducts()
    showEdit.value = false
    showToast('Produk berhasil diperbarui!')
  } catch (error) {
    console.error('Error updating product:', error)
    showToast(error.response?.data?.message || 'Gagal mengupdate produk', 'error')
  }
}

function showRestock(p) {
  selectedProduct.value = p
  restockData.value = {
    batch_number: 'BCH-' + Date.now(),
    qty: 0,
    buy_price: p.batches?.[0]?.buy_price || 0,
    sell_price: p.batches?.[0]?.sell_price || 0,
    expired_date: ''
  }
  showRestockModal.value = true
}

async function handleRestock() {
  try {
    await inventoryStore.addStock(selectedProduct.value.id, restockData.value)
    showRestockModal.value = false
    showToast('Stok berhasil ditambahkan!')
  } catch (err) {
    showToast(String(err), 'error')
  }
}

async function saveProduct() {
  if (!newProd.value.name || !newProd.value.name.trim()) {
    showToast('Nama produk wajib diisi.', 'error')
    return
  }
  if (!newProd.value.initial_stock || newProd.value.initial_stock < 1) {
    showToast('Stok awal wajib diisi minimal 1.', 'error')
    return
  }
  try {
    const productData = {
      name: newProd.value.name,
      sku: newProd.value.sku,
      category: newProd.value.category,
      unit: newProd.value.unit,
      min_stock: newProd.value.min_stock,
      base_buy_price: newProd.value.buy_price || 0,
      base_sell_price: newProd.value.sell_price || 0,
      initial_stock: newProd.value.initial_stock
    }
    await inventoryStore.addProduct(productData)
    await inventoryStore.fetchProducts()
    
    showAdd.value = false
    newProd.value = { name: '', sku: '', category: '', unit: 'kg', min_stock: 5, buy_price: 0, sell_price: 0, initial_stock: 1 }
    showToast('Produk baru berhasil ditambahkan dengan stok awal!')
  } catch (err) {
    showToast(String(err), 'error')
  }
}

function formatNum(n) { return (n || 0).toLocaleString('id-ID') }

async function deleteProduct(p) {
  if (!confirm(`Apakah Anda yakin ingin menghapus "${p.name}"?`)) return
  try {
    await inventoryStore.deleteProduct(p.id)
    showToast('Produk berhasil dihapus!')
  } catch (err) {
    showToast(String(err), 'error')
  }
}
</script>


<style scoped>
.inventory-page { max-width: 1200px; }

.page-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  margin-bottom: var(--spacing-xl);
}
.page-title    { font-size: var(--font-3xl); font-weight: 700; }
.page-subtitle { font-size: var(--font-sm); color: var(--color-text-muted); margin-top: 4px; }
.btn-add-product {
  display: flex; align-items: center; gap: 8px;
  background: var(--color-primary); color: #fff;
  padding: 12px 20px; border-radius: var(--radius-md);
  font-size: var(--font-base); font-weight: 700;
  transition: background var(--transition-fast);
}
.btn-add-product:hover { background: var(--color-primary-hover); }

/* Stats */
.inv-stats {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: var(--spacing-md); margin-bottom: var(--spacing-xl);
}
.inv-stat {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg);
}
.inv-stat-label { font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); letter-spacing: 0.5px; margin-bottom: 8px; }
.inv-stat-val { font-size: var(--font-3xl); font-weight: 800; color: var(--color-text-primary); margin-bottom: 8px; }
.inv-stat-val.danger { color: var(--color-danger); }
.inv-stat-val.sm { font-size: var(--font-xl); }
.inv-stat-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: var(--radius-full);
  font-size: var(--font-xs);
  font-weight: 700;
}
.inv-stat-badge.ok     { background: var(--color-primary-light); color: var(--color-primary); }
.inv-stat-badge.warn   { background: var(--color-warning-light); color: var(--color-warning); }
.inv-stat-badge.danger { background: var(--color-danger-light);  color: var(--color-danger); }

/* Toolbar */
.toolbar {
  display: flex; gap: var(--spacing-md);
  margin-bottom: var(--spacing-lg);
}
.search-box {
  flex: 1;
  display: flex; align-items: center; gap: 10px;
  background: #fff;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 0 14px;
  color: var(--color-text-muted);
}
.search-box:focus-within { border-color: var(--color-primary); }
.search-box input { flex: 1; border: none; padding: 12px 0; font-size: var(--font-base); outline: none; }
.select-filter {
  padding: 12px 14px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: var(--font-sm);
  font-weight: 600;
  color: var(--color-text-secondary);
  background: #fff;
  cursor: pointer;
}
.select-filter:focus { outline: none; border-color: var(--color-primary); }

/* Table */
.table-card {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}
.table-head-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr 80px 1fr 1fr 80px;
  padding: 12px 20px;
  background: #F8FAFC;
  border-bottom: 1px solid var(--color-border);
  font-size: var(--font-xs);
  font-weight: 700;
  color: var(--color-text-muted);
  letter-spacing: 0.5px;
  gap: 8px;
}
.table-data-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr 80px 1fr 1fr 80px;
  align-items: center;
  padding: 14px 20px;
  border-bottom: 1px solid var(--color-border);
  transition: background var(--transition-fast);
  gap: 8px;
}
.table-data-row:last-of-type { border-bottom: none; }
.table-data-row:hover { background: #F8FAFC; }

.product-info { display: flex; align-items: center; gap: 10px; }
.product-icon {
  width: 36px; height: 36px;
  background: #F1F5F9;
  border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.prod-name { font-size: var(--font-base); font-weight: 600; }
.prod-unit { font-size: var(--font-xs); color: var(--color-text-muted); }
.sku-text  { font-size: var(--font-xs); font-family: monospace; color: var(--color-text-muted); }
.cat-pill  {
  display: inline-block;
  padding: 4px 10px;
  background: #F1F5F9;
  border-radius: var(--radius-full);
  font-size: var(--font-xs);
  font-weight: 600;
  color: var(--color-text-secondary);
}

.stock-cell { display: flex; align-items: center; justify-content: center; text-align: center; }
.text-center { text-align: center; }
.stock-num { font-size: var(--font-base); font-weight: 700; }
.stock-num.ok    { color: var(--color-primary); }
.stock-num.low   { color: var(--color-warning); }
.stock-num.empty { color: var(--color-danger); }

.min-stock  { font-size: var(--font-sm); color: var(--color-text-muted); }
.price-text { font-size: var(--font-sm); font-weight: 600; color: var(--color-text-secondary); }
.price-text.sell { color: var(--color-primary); }

.action-btns { display: flex; gap: 6px; }
.act-btn {
  width: 30px; height: 30px;
  border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition-fast);
}
.act-btn.edit    { border: 1px solid var(--color-border); color: var(--color-text-secondary); }
.act-btn.edit:hover { border-color: var(--color-secondary); color: var(--color-secondary); }
.act-btn.restock { border: 1px solid var(--color-primary-light); color: var(--color-primary); background: var(--color-primary-light); }
.act-btn.restock:hover { background: var(--color-primary); color: #fff; }
.act-btn.delete { border: 1px solid var(--color-danger-light); color: var(--color-danger); background: var(--color-danger-light); }
.act-btn.delete:hover { background: var(--color-danger); color: #fff; }

.table-footer {
  padding: 14px 20px;
  font-size: var(--font-sm);
  color: var(--color-text-muted);
  border-top: 1px solid var(--color-border);
  background: #F8FAFC;
}

/* Add Modal */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(15, 23, 42, 0.7);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000;
}
.modal-box {
  background: #fff; border-radius: var(--radius-xl);
  width: 580px; max-width: 95vw;
  padding: var(--spacing-xl);
  box-shadow: 0 25px 50px rgba(15, 23, 42, 0.3);
  animation: slideUp 0.2s ease;
}
@keyframes slideUp { from { transform: translateY(24px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: var(--spacing-lg);
}
.modal-title { font-size: var(--font-xl); font-weight: 700; }
.close-btn { color: var(--color-text-muted); padding: 6px; border-radius: var(--radius-sm); }
.close-btn:hover { background: #F1F5F9; }

.add-form { display: flex; flex-direction: column; gap: var(--spacing-md); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md); }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: var(--font-xs); font-weight: 700; color: var(--color-text-muted); }
.form-input {
  padding: 11px 14px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: var(--font-base);
  transition: border-color var(--transition-fast);
  font-family: inherit;
}
.form-input:focus { outline: none; border-color: var(--color-primary); }

.modal-actions { display: flex; gap: 12px; margin-top: var(--spacing-sm); }
.btn-cancel {
  flex: 1; padding: 13px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  font-weight: 600; color: var(--color-text-secondary);
}
.btn-cancel:hover { border-color: var(--color-danger); color: var(--color-danger); }
.btn-confirm {
  flex: 2; padding: 13px;
  background: var(--color-primary); color: #fff;
  border-radius: var(--radius-md);
  font-weight: 700;
  transition: background var(--transition-fast);
}
.btn-confirm:hover { background: var(--color-primary-hover); }

.btn-manage-cat {
  display: flex; align-items: center; gap: 8px;
  background: #F1F5F9; color: var(--color-text-secondary);
  border: 1.5px solid var(--color-border);
  padding: 12px 16px; border-radius: var(--radius-md);
  font-size: var(--font-sm); font-weight: 600;
  transition: all var(--transition-fast);
  cursor: pointer;
  height: 48px;
  align-self: center;
}
.btn-manage-cat:hover {
  background: var(--color-primary-light);
  color: var(--color-primary);
  border-color: var(--color-primary-light);
}
</style>
