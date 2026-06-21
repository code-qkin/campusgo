<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import {
  MapPin, Clock, Plus, Tag, X, CheckCircle, CornerDownRight, Pencil, Trash2, Phone, MessageCircle
} from 'lucide-vue-next';
import { api } from '../api';
import { showAlert as alert } from '../alert';

const currentUser = JSON.parse(localStorage.getItem('campusgo_user') || '{}');
const currentUserId = currentUser.id;

const items = ref<any[]>([]);
const isLoading = ref(false);
const activeReports = ref<any[]>([]);
const selectedImage = ref<File | null>(null);
const filterCategory = ref('All');
const editingItem = ref<any | null>(null);

const reportModalOpen = ref(false);
const newItemName = ref('');
const newItemCategory = ref<'Electronics' | 'Keys' | 'Bags' | 'Student IDs' | 'Others'>('Electronics');
const newItemLocation = ref('');
const newItemDescription = ref('');
const newItemPhone = ref('');

const fetchItems = async () => {
  isLoading.value = true;
  try {
    const data = await api.get('/lost-found');
    items.value = data;
    activeReports.value = data.filter((i: any) => i.reporter_id === currentUserId);
  } catch (e) {
    alert('Failed to fetch items.');
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => fetchItems());

const openEdit = (item: any) => {
  editingItem.value = item;
  newItemName.value = item.name;
  newItemCategory.value = item.category;
  newItemLocation.value = item.location;
  newItemDescription.value = item.description || '';
  newItemPhone.value = item.contact_phone || '';
  reportModalOpen.value = true;
};

const handleCreateReport = async () => {
  if (!newItemName.value || !newItemLocation.value || !newItemPhone.value) {
    alert('Please fill in item name, location and phone number');
    return;
  }

  try {
    if (editingItem.value) {
      const data = await api.patch(`/lost-found/${editingItem.value.id}`, {
        name: newItemName.value,
        category: newItemCategory.value,
        location: newItemLocation.value,
        description: newItemDescription.value,
        contact_phone: newItemPhone.value,
      });
      const idx = items.value.findIndex(i => i.id === editingItem.value?.id);
      if (idx !== -1) items.value[idx] = data;
      editingItem.value = null;
      alert('Item updated successfully');
    } else {
      const formData = new FormData();
      formData.append('name', newItemName.value);
      formData.append('category', newItemCategory.value);
      formData.append('location', newItemLocation.value);
      formData.append('description', newItemDescription.value);
      formData.append('contact_phone', newItemPhone.value);
      if (selectedImage.value) formData.append('image', selectedImage.value);
      const data = await api.postForm('/lost-found', formData);
      items.value.unshift(data);
      activeReports.value.unshift(data);
      alert('Item reported successfully');
    }
    reportModalOpen.value = false;
    newItemName.value = '';
    newItemLocation.value = '';
    newItemDescription.value = '';
    newItemPhone.value = '';
    selectedImage.value = null;
  } catch (e: any) {
    alert(e.message || 'Failed to save item');
  }
};

const handleDelete = async (id: number) => {
  if (!confirm('Delete this item?')) return;
  try {
    await api.delete(`/lost-found/${id}`);
    items.value = items.value.filter(i => i.id !== id);
    activeReports.value = activeReports.value.filter(i => i.id !== id);
    alert('Item deleted');
  } catch (e: any) {
    alert(e.message || 'Failed to delete item');
  }
};

const handleMarkClaimed = async (id: number) => {
  if (!confirm('Mark this item as claimed? This means it has been handed over.')) return;
  try {
    const data = await api.patch(`/lost-found/${id}/mark-claimed`);
    const idx = items.value.findIndex(i => i.id === id);
    if (idx !== -1) items.value[idx] = data;
    alert('Item marked as claimed');
  } catch (e: any) {
    alert(e.message || 'Failed to update item');
  }
};

const callNumber = (phone: string | null) => {
  if (!phone) {
    alert('No phone number available for this item');
    return;
  }
  window.location.href = `tel:${phone}`;
};

const whatsappNumber = (phone: string | null) => {
  if (!phone) {
    alert('No phone number available for this item');
    return;
  }
  const cleaned = phone.replace(/\D/g, '');
  const formatted = cleaned.startsWith('0') ? '234' + cleaned.slice(1) : cleaned;
  window.open(`https://wa.me/${formatted}`, '_blank');
};

const showClaimed = ref(false)

const filteredItems = computed(() => {
  let result = items.value;

  // filter by claimed status first
  result = result.filter(item => showClaimed.value ? item.is_claimed : !item.is_claimed);

  if (filterCategory.value !== 'All') {
    result = result.filter(item => item.category === filterCategory.value);
  }
  return result;
});

const categories = ["All", "Electronics", "Keys", "Bags", "Student IDs", "Others"];
</script>

<template>
  <div
    class="flex-grow flex max-md:flex-col h-[calc(100vh-64px)] max-md:h-[calc(100vh-128px)] max-md:overflow-y-auto overflow-hidden mt-16 max-md:ml-0 ml-[280px] bg-[#131313] relative font-sans">

    <button @click="() => { editingItem = null; reportModalOpen = true; }"
      class="fixed bottom-10 right-[480px] max-md:right-6 max-md:bottom-24 w-14 h-14 bg-brand-primary-container hover:brightness-110 text-white rounded-full flex items-center justify-center hover:scale-110 active:scale-95 transition-all duration-300 cursor-pointer shadow-[0_8px_30px_rgba(255,95,82,0.4)] z-40 outline-none border-none group">
      <Plus class="w-6 h-6 group-hover:rotate-90 transition-transform duration-300" />
    </button>

    <section class="flex-1 flex flex-col max-md:p-4 p-10 max-md:overflow-visible overflow-y-auto">
      <div class="flex justify-between items-end mb-8 select-none">
        <div>
          <h2 class="text-3xl font-bold text-on-surface mb-2 tracking-tight">Lost & Found</h2>
          <p class="text-sm text-on-surface-variant font-light">Report items and contact the finder directly.</p>
        </div>
      </div>

      <div v-if="isLoading" class="flex items-center justify-center py-20">
        <div class="w-6 h-6 rounded-full border-2 border-brand-primary/20 border-t-brand-primary animate-spin"></div>
      </div>

      <template v-else>
        <div class="flex max-md:flex-col flex-row items-start md:items-center justify-between gap-3 mb-8">
          <div class="flex gap-2.5 overflow-x-auto pb-1 select-none whitespace-nowrap scrollbar-none w-full md:w-auto">
            <button v-for="cat in categories" :key="cat" @click="filterCategory = cat"
              class="px-4 py-2 rounded-lg border text-xs font-bold transition-all cursor-pointer" :class="filterCategory === cat
                ? 'bg-brand-primary/10 border-brand-primary text-brand-primary'
                : 'bg-[#201f1f] border-[#222] text-on-surface-variant hover:border-[#353534] hover:text-white'">
              {{ cat }}
            </button>
          </div>

          <!-- Active / Claimed toggle -->
          <div class="flex bg-[#1e1e1e] border border-[#2d2d2d] rounded-lg p-1 gap-1 shrink-0">
            <button @click="showClaimed = false"
              class="px-3 py-1.5 text-xs font-bold rounded-md transition-all cursor-pointer border-none" :class="!showClaimed
                ? 'bg-brand-primary-container text-white'
                : 'text-on-surface-variant bg-transparent hover:text-on-surface'">
              Active
            </button>
            <button @click="showClaimed = true"
              class="px-3 py-1.5 text-xs font-bold rounded-md transition-all cursor-pointer border-none" :class="showClaimed
                ? 'bg-brand-primary-container text-white'
                : 'text-on-surface-variant bg-transparent hover:text-on-surface'">
              Claimed
            </button>
          </div>
        </div>

        <div v-if="filteredItems.length === 0"
          class="bg-[#201f1f] border border-dashed border-[#2d2d2d] rounded-xl p-12 text-center select-none">
          <Tag class="w-10 h-10 text-on-surface-variant/50 mx-auto mb-3" />
          <p class="text-sm text-on-surface font-semibold">No items reported in this category.</p>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 pb-12">
          <div v-for="item in filteredItems" :key="item.id"
            class="bg-[#1e1e1e]/60 border border-[#2d2d2d] rounded-xl overflow-hidden flex flex-col hover:border-[#353534] transition-all group">

            <div class="h-44 bg-[#131313] relative overflow-hidden flex items-center justify-center">
              <img v-if="item.image_url" :alt="item.name"
                class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-500"
                :src="item.image_url" />
              <div v-else class="w-full h-full flex items-center justify-center">
                <Tag class="w-10 h-10 text-on-surface-variant/30" />
              </div>
              <span
                class="absolute top-3 left-3 bg-[#131313]/90 backdrop-blur border border-[#2d2d2d] text-xs font-bold px-2 py-1 rounded text-brand-secondary select-none">
                {{ item.category }}
              </span>
            </div>

            <div class="p-5 flex-1 flex flex-col">
              <h3 class="text-base font-bold text-on-surface">{{ item.name }}</h3>
              <p class="text-xs text-on-surface-variant mt-1.5 leading-relaxed font-light flex-1">
                {{ item.description || 'No description provided.' }}
              </p>

              <div
                class="space-y-2 mt-5 pt-4 border-t border-[#353534]/50 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">
                <div class="flex items-center gap-1.5">
                  <MapPin class="w-3.5 h-3.5" />
                  <span>Found:</span>
                  <span class="text-on-surface font-bold ml-auto">{{ item.location }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                  <Clock class="w-3.5 h-3.5" />
                  <span>Reported:</span>
                  <span class="text-on-surface font-bold ml-auto">{{ new Date(item.created_at).toLocaleDateString()
                  }}</span>
                </div>
              </div>

              <!-- Action buttons -->
              <div class="mt-5">
                <!-- Own item -->
                <div v-if="item.reporter_id === currentUserId" class="space-y-2">
                  <!-- Active item — show edit, delete, mark claimed -->
                  <template v-if="!item.is_claimed">
                    <div class="flex gap-2">
                      <button @click="openEdit(item)"
                        class="flex-1 flex items-center justify-center gap-1.5 bg-[#2a2a2a] text-on-surface-variant text-xs font-bold py-2.5 rounded-lg cursor-pointer border-none hover:text-white transition-all">
                        <Pencil class="w-3.5 h-3.5" /> Edit
                      </button>
                      <button @click="handleDelete(item.id)"
                        class="flex-1 flex items-center justify-center gap-1.5 bg-[#2a2a2a] text-[#ff5f52] text-xs font-bold py-2.5 rounded-lg cursor-pointer border-none hover:brightness-110 transition-all">
                        <Trash2 class="w-3.5 h-3.5" /> Delete
                      </button>
                    </div>
                    <button @click="handleMarkClaimed(item.id)"
                      class="w-full flex items-center justify-center gap-1.5 bg-brand-tertiary/10 text-brand-tertiary text-xs font-bold py-2.5 rounded-lg cursor-pointer border-none hover:brightness-110 transition-all">
                      <CheckCircle class="w-3.5 h-3.5" /> Mark as Claimed
                    </button>
                  </template>

                  <!-- Claimed — disabled state, no buttons -->
                  <div v-else
                    class="w-full py-2.5 rounded-lg border border-brand-tertiary/30 bg-brand-tertiary/10 text-brand-tertiary font-bold text-xs text-center flex items-center justify-center gap-1.5">
                    <CheckCircle class="w-4 h-4" /> Claimed
                  </div>
                </div>

                <!-- Already claimed -->
                <div v-else-if="item.is_claimed"
                  class="w-full py-2.5 rounded-lg border border-brand-tertiary/30 bg-brand-tertiary/10 text-brand-tertiary font-bold text-xs text-center flex items-center justify-center gap-1.5">
                  <CheckCircle class="w-4 h-4" /> Already Claimed
                </div>

                <!-- Contact buttons -->
                <div v-else class="flex gap-2">
                  <button @click="callNumber(item.contact_phone)"
                    class="flex-1 flex items-center justify-center gap-1.5 bg-[#2a2a2a] text-on-surface text-xs font-bold py-2.5 rounded-lg cursor-pointer border-none hover:bg-[#333] transition-all">
                    <Phone class="w-3.5 h-3.5" /> Call
                  </button>
                  <button @click="whatsappNumber(item.contact_phone)"
                    class="flex-1 flex items-center justify-center gap-1.5 bg-brand-tertiary/15 text-brand-tertiary text-xs font-bold py-2.5 rounded-lg cursor-pointer border-none hover:brightness-110 transition-all">
                    <MessageCircle class="w-3.5 h-3.5" /> WhatsApp
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </section>

    <!-- RIGHT PANEL -->
    <aside
      class="max-md:w-full w-[450px] border-l border-[#2d2d2d] bg-[#1c1b1b] p-6 flex flex-col max-md:h-auto h-full shrink-0 select-none">
      <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-4">My Report Logs</h3>
      <div v-if="activeReports.length === 0" class="flex-1 flex items-center justify-center">
        <p class="text-xs text-on-surface-variant text-center">No reports yet.</p>
      </div>
      <div v-else class="space-y-4 flex-1 overflow-y-auto">
        <div v-for="report in activeReports" :key="report.id"
          class="p-4 rounded-xl bg-[#131313] border border-[#2d2d2d] space-y-3">
          <div class="flex justify-between items-start">
            <span
              class="px-2 py-0.5 rounded text-xs font-black uppercase tracking-wider bg-brand-primary-container text-white">lost</span>
            <div class="flex items-center gap-1 text-xs font-bold">
              <CheckCircle class="w-3.5 h-3.5 text-brand-tertiary" />
              <span class="text-brand-tertiary">{{ report.is_claimed ? 'Claimed' : 'Open' }}</span>
            </div>
          </div>
          <div>
            <h4 class="text-xs font-bold text-on-surface">{{ report.name }}</h4>
            <p class="text-xs text-on-surface-variant font-light leading-relaxed mt-1">{{ report.description }}</p>
            <div class="flex items-center gap-1.5 text-xs text-on-surface-variant/60 font-semibold mt-3">
              <CornerDownRight class="w-3.5 h-3.5 text-on-surface-variant/45" />
              <span>{{ new Date(report.created_at).toLocaleDateString() }}</span>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <!-- MODAL: Report / Edit Item -->
    <div v-if="reportModalOpen" class="fixed inset-0 z-50 flex items-center justify-center max-md:p-4 p-8">
      <div class="absolute inset-0 bg-black/85 backdrop-blur-xs" @click="reportModalOpen = false"></div>
      <div
        class="relative bg-[#1e1e1e] border border-[#2d2d2d] rounded-xl max-md:p-5 p-8 max-w-[480px] w-full max-h-[90vh] overflow-y-auto shadow-2xl animate-in zoom-in-95 duration-200">
        <header class="flex justify-between items-center mb-6">
          <h2 class="text-lg font-bold text-on-surface">{{ editingItem ? 'Edit Item' : 'Report Lost Property' }}</h2>
          <button @click="reportModalOpen = false"
            class="w-8 h-8 rounded-full hover:bg-[#2a2a2a] flex items-center justify-center text-on-surface-variant cursor-pointer border-none bg-transparent">
            <X class="w-5 h-5" />
          </button>
        </header>

        <form @submit.prevent="handleCreateReport" class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Item Name</label>
            <input type="text" v-model="newItemName"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary"
              placeholder="e.g., Red Leather Wallet" required />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Category</label>
            <select v-model="newItemCategory"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary">
              <option value="Electronics">Electronics</option>
              <option value="Keys">Keys</option>
              <option value="Bags">Bags</option>
              <option value="Student IDs">Student IDs</option>
              <option value="Others">Others</option>
            </select>
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Found At </label>
            <input type="text" v-model="newItemLocation"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary"
              placeholder="e.g., SUB, Hostel B Common Room" required />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Your Phone Number</label>
            <input type="tel" v-model="newItemPhone"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary"
              placeholder="e.g., 08012345678" required />
          </div>
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Description</label>
            <textarea v-model="newItemDescription" rows="3"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3 py-2 text-xs text-on-surface focus:outline-none focus:border-brand-primary"
              placeholder="Describe unique identifiers..." />
          </div>
          <div v-if="!editingItem" class="space-y-1.5">
            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Image (optional)</label>
            <input type="file" accept="image/*"
              @change="(e) => selectedImage = (e.target as HTMLInputElement).files?.[0] || null"
              class="w-full bg-[#131313] border border-[#2d2d2d] rounded px-3 py-2 text-xs text-on-surface-variant cursor-pointer" />
          </div>
          <button type="submit"
            class="w-full py-3 bg-brand-primary-container hover:brightness-110 text-white font-bold text-xs rounded-lg cursor-pointer border-none mt-2">
            {{ editingItem ? 'Save Changes' : 'Submit Report' }}
          </button>
        </form>
      </div>
    </div>

  </div>
</template>