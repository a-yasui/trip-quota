<template>
  <div>
    <form :action="formAction" method="POST">
      <slot name="csrf"></slot>
      <slot name="method"></slot>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 基本情報 -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700">{{ __('説明') }} <span class="text-red-500">*</span></label>
          <input
            type="text"
            name="description"
            id="description"
            v-model="formData.description"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            required
          >
          <slot name="description_error"></slot>
        </div>

        <div>
          <label for="expense_date" class="block text-sm font-medium text-gray-700">{{ __('支出日') }} <span class="text-red-500">*</span></label>
          <input
            type="date"
            name="expense_date"
            id="expense_date"
            v-model="formData.expense_date"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            required
          >
          <slot name="expense_date_error"></slot>
        </div>

        <div>
          <label for="amount" class="block text-sm font-medium text-gray-700">{{ __('金額') }} <span class="text-red-500">*</span></label>
          <input
            type="number"
            name="amount"
            id="amount"
            v-model="formData.amount"
            step="0.01"
            min="0"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            required
          >
          <slot name="amount_error"></slot>
        </div>

        <div>
          <label for="currency" class="block text-sm font-medium text-gray-700">{{ __('通貨') }} <span class="text-red-500">*</span></label>
          <select
            id="currency"
            name="currency"
            v-model="formData.currency"
            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            required
          >
            <option
              v-for="(label, value) in currencies"
              :key="value"
              :value="value"
            >
              {{ label }}
            </option>
          </select>
          <slot name="currency_error"></slot>
        </div>

        <div>
          <label for="category" class="block text-sm font-medium text-gray-700">{{ __('カテゴリ') }}</label>
          <select
            id="category"
            name="category"
            v-model="formData.category"
            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          >
            <option value="">{{ __('選択してください') }}</option>
            <option value="food">{{ __('食事') }}</option>
            <option value="transportation">{{ __('交通') }}</option>
            <option value="accommodation">{{ __('宿泊') }}</option>
            <option value="entertainment">{{ __('娯楽') }}</option>
            <option value="shopping">{{ __('買い物') }}</option>
            <option value="other">{{ __('その他') }}</option>
          </select>
          <slot name="category_error"></slot>
        </div>

        <div>
          <label for="payer_member_id" class="block text-sm font-medium text-gray-700">{{ __('支払者') }} <span class="text-red-500">*</span></label>
          <select
            id="payer_member_id"
            name="payer_member_id"
            v-model="formData.payer_member_id"
            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            required
          >
            <option value="">{{ __('選択してください') }}</option>
            <option
              v-for="member in members"
              :key="member.id"
              :value="member.id"
            >
              {{ member.name }}
            </option>
          </select>
          <slot name="payer_member_id_error"></slot>
        </div>

        <!-- メモ -->
        <div class="md:col-span-2">
          <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('メモ') }}</label>
          <textarea
            name="notes"
            id="notes"
            rows="3"
            v-model="formData.notes"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
          ></textarea>
          <slot name="notes_error"></slot>
        </div>

        <!-- 参加メンバー -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('参加メンバー') }}</label>

          <member-selector
            class="vue-member-selector"
            :branch-groups="branchGroups"
            :members="members"
            :initial-selected-members="selectedMemberIds"
            input-name="member_ids"
            @update:selected="updateSelectedMembers"
          ></member-selector>
          <slot name="member_ids_error"></slot>
        </div>

        <!-- メンバーごとの支払い金額 -->
        <div v-if="formData.amount > 0 && selectedMembersList.length > 0" class="md:col-span-2 mt-4">
          <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('メンバーごとの支払い金額') }}</h3>
          
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="mb-2 flex justify-between">
              <span class="text-sm font-medium text-gray-700">{{ __('合計金額') }}: {{ formData.amount }} {{ formData.currency }}</span>
              <button 
                type="button" 
                class="text-sm text-lime-600 hover:text-lime-700"
                @click="resetShareAmounts"
              >
                {{ __('均等に分配') }}
              </button>
            </div>
            
            <div class="space-y-3">
              <div v-for="member in selectedMembersList" :key="member.id" class="flex items-center">
                <div class="w-1/3">
                  <span class="text-sm font-medium text-gray-700">{{ member.name }}</span>
                  <span v-if="formData.payer_member_id == member.id" class="ml-2 text-xs bg-lime-100 text-lime-800 px-2 py-0.5 rounded-full">
                    {{ __('支払者') }}
                  </span>
                </div>
                <div class="w-2/3">
                  <input
                    type="number"
                    :name="`member_share_amounts[${member.id}]`"
                    v-model="memberShareAmounts[member.id]"
                    step="0.01"
                    min="0"
                    :max="formData.amount"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    @input="updateTotalShareAmount"
                  >
                </div>
              </div>
              
              <!-- 合計と差額の表示 -->
              <div class="flex justify-between pt-3 border-t border-gray-200">
                <span class="text-sm font-medium text-gray-700">{{ __('分配合計') }}: {{ totalShareAmount }}</span>
                <span 
                  :class="{'text-red-600': !isShareAmountValid, 'text-green-600': isShareAmountValid}"
                  class="text-sm font-medium"
                >
                  {{ __('差額') }}: {{ shareAmountDifference }}
                </span>
              </div>
              
              <div v-if="!isShareAmountValid" class="text-sm text-red-600">
                {{ __('分配合計が支出金額と一致していません。') }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-end">
        <slot name="cancel_button">
          <a :href="cancelUrl" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
            キャンセル
          </a>
        </slot>
        <slot name="submit_button">
          <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500">
            保存
          </button>
        </slot>
      </div>
    </form>
  </div>
</template>

<script>
import MemberSelector from './MemberSelector.vue';

export default {
  components: {
    MemberSelector
  },
  props: {
    expense: {
      type: Object,
      default: null
    },
    travelPlan: {
      type: Object,
      required: true
    },
    branchGroups: {
      type: Array,
      required: true
    },
    members: {
      type: Array,
      required: true
    },
    selectedMemberIds: {
      type: Array,
      default: () => []
    },
    formAction: {
      type: String,
      required: true
    },
    cancelUrl: {
      type: String,
      required: true
    },
    oldValues: {
      type: Object,
      default: () => ({})
    },
    currencies: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      formData: {
        description: this.getInitialValue('description', this.expense ? this.expense.description : ''),
        amount: this.getInitialValue('amount', this.expense ? this.expense.amount : ''),
        currency: this.getInitialValue('currency', this.expense ? this.expense.currency : 'JPY'),
        expense_date: this.getInitialValue('expense_date', this.expense ? this.formatDate(this.expense.expense_date) : this.formatDate(new Date())),
        category: this.getInitialValue('category', this.expense ? this.expense.category : ''),
        notes: this.getInitialValue('notes', this.expense ? this.expense.notes : ''),
        payer_member_id: this.getInitialValue('payer_member_id', this.expense ? this.expense.payer_member_id : ''),
      },
      selectedMembersList: [],
      memberShareAmounts: {},
      totalShareAmount: 0
    }
  },
  computed: {
    // 分配金額の合計と支出金額の差額
    shareAmountDifference() {
      return (this.formData.amount - this.totalShareAmount).toFixed(2);
    },
    // 分配金額の合計が支出金額と一致しているか
    isShareAmountValid() {
      return Math.abs(this.formData.amount - this.totalShareAmount) < 0.01;
    }
  },
  watch: {
    // 金額が変更されたら分配金額を再計算
    'formData.amount': function(newVal) {
      if (newVal > 0 && this.selectedMembersList.length > 0) {
        this.resetShareAmounts();
      }
    }
  },
  created() {
    // 初期選択メンバーを設定
    this.initializeSelectedMembers();
    
    // 既存の経費データがある場合、メンバーごとの分配金額を設定
    if (this.expense && this.expense.members) {
      this.initializeShareAmounts();
    } else {
      this.resetShareAmounts();
    }
  },
  mounted() {
    // コンポーネントがマウントされた後に、選択されたメンバーの分配金額を初期化
    this.$nextTick(() => {
      if (this.expense && this.expense.members) {
        this.initializeShareAmounts();
      }
    });
  },
  methods: {
    // __ 関数の定義（Laravel Blade の __ 関数の代替）
    __(text) {
      return text; // 実際の翻訳機能が必要な場合は、ここを拡張
    },

    // 初期値を取得
    getInitialValue(field, defaultValue) {
      // oldValues（バリデーションエラー時の入力値）があればそれを優先
      if (this.oldValues && this.oldValues[field] !== undefined) {
        return this.oldValues[field];
      }
      // デフォルト値を返す
      return defaultValue;
    },

    // 日付をフォーマット
    formatDate(date) {
      if (!date) return '';
      
      if (typeof date === 'string') {
        date = new Date(date);
      }
      
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      
      return `${year}-${month}-${day}`;
    },
    
    // 選択されたメンバーを更新
    updateSelectedMembers(selectedIds) {
      this.selectedMembersList = this.members.filter(member => selectedIds.includes(member.id));
      
      // 新しく選択されたメンバーの分配金額を初期化
      this.selectedMembersList.forEach(member => {
        if (this.memberShareAmounts[member.id] === undefined) {
          // Vue 3では$setの代わりに直接代入を使用
          this.memberShareAmounts[member.id] = 0;
        }
      });
      
      // 選択されなくなったメンバーの分配金額を削除
      Object.keys(this.memberShareAmounts).forEach(id => {
        if (!selectedIds.includes(Number(id))) {
          // Vue 3では$deleteの代わりにdeleteを使用
          delete this.memberShareAmounts[id];
        }
      });
      
      // 分配金額を再計算
      this.resetShareAmounts();
    },
    
    // 初期選択メンバーを設定
    initializeSelectedMembers() {
      this.selectedMembersList = this.members.filter(member => this.selectedMemberIds.includes(member.id));
    },
    
    // 既存の経費データからメンバーごとの分配金額を初期化
    initializeShareAmounts() {
      if (this.expense && this.expense.members) {
        this.expense.members.forEach(member => {
          // Vue 3では$setの代わりに直接代入を使用
          if (member.pivot && member.pivot.share_amount !== undefined) {
            this.memberShareAmounts[member.id] = parseFloat(member.pivot.share_amount);
          } else if (member.share_amount !== undefined) {
            // 場合によっては、pivotではなく直接share_amountが設定されている場合がある
            this.memberShareAmounts[member.id] = parseFloat(member.share_amount);
          } else {
            // どちらも存在しない場合は0を設定
            this.memberShareAmounts[member.id] = 0;
          }
        });
        this.updateTotalShareAmount();
      } else {
        this.resetShareAmounts();
      }
    },
    
    // 分配金額を均等に再設定
    resetShareAmounts() {
      if (this.selectedMembersList.length === 0 || !this.formData.amount) return;
      
      const shareAmount = parseFloat(this.formData.amount) / this.selectedMembersList.length;
      const roundedShareAmount = Math.round(shareAmount * 100) / 100; // 小数点2桁で四捨五入
      
      this.selectedMembersList.forEach(member => {
        // Vue 3では$setの代わりに直接代入を使用
        this.memberShareAmounts[member.id] = roundedShareAmount;
      });
      
      // 端数調整（最後のメンバーに調整額を加算）
      if (this.selectedMembersList.length > 0) {
        const lastMemberId = this.selectedMembersList[this.selectedMembersList.length - 1].id;
        const totalBeforeAdjustment = roundedShareAmount * this.selectedMembersList.length;
        const adjustment = parseFloat(this.formData.amount) - totalBeforeAdjustment;
        
        if (Math.abs(adjustment) > 0.001) { // 誤差が十分小さい場合は調整しない
          this.memberShareAmounts[lastMemberId] = Math.round((roundedShareAmount + adjustment) * 100) / 100;
        }
      }
      
      this.updateTotalShareAmount();
    },
    
    // 分配金額の合計を更新
    updateTotalShareAmount() {
      this.totalShareAmount = Object.values(this.memberShareAmounts).reduce((sum, amount) => {
        return sum + parseFloat(amount || 0);
      }, 0).toFixed(2);
    }
  }
}
</script>
