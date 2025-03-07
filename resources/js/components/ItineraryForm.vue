<template>
  <div>
    <form :action="formAction" method="POST">
      <slot name="csrf"></slot>
      <slot name="method"></slot>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 交通手段 -->
        <div>
          <label for="transportation_type" class="block text-sm font-medium text-gray-700">{{ __('交通手段') }} <span class="text-red-500">*</span></label>
          <select 
            id="transportation_type" 
            name="transportation_type" 
            v-model="formData.transportation_type"
            @change="updateFlightFields"
            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
            required
          >
            <option value="">{{ __('選択してください') }}</option>
            <option 
              v-for="type in transportationTypes" 
              :key="type.value" 
              :value="type.value"
            >
              {{ getTransportationLabel(type.value) }}
            </option>
          </select>
          <slot name="transportation_type_error"></slot>
        </div>

        <!-- 出発地 -->
        <div>
          <label for="departure_location" class="block text-sm font-medium text-gray-700">{{ __('出発地') }} <span class="text-red-500">*</span></label>
          <input 
            type="text" 
            name="departure_location" 
            id="departure_location" 
            v-model="formData.departure_location"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
            required
          >
          <slot name="departure_location_error"></slot>
        </div>

        <!-- 到着地 -->
        <div>
          <label for="arrival_location" class="block text-sm font-medium text-gray-700">{{ __('到着地') }} <span class="text-red-500">*</span></label>
          <input 
            type="text" 
            name="arrival_location" 
            id="arrival_location" 
            v-model="formData.arrival_location"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
            required
          >
          <slot name="arrival_location_error"></slot>
        </div>

        <!-- 出発時刻 -->
        <div>
          <label for="departure_time" class="block text-sm font-medium text-gray-700">{{ __('出発時刻') }} <span class="text-red-500">*</span></label>
          <input 
            type="datetime-local" 
            name="departure_time" 
            id="departure_time" 
            v-model="formData.departure_time"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
            required
          >
          <slot name="departure_time_error"></slot>
        </div>

        <!-- 出発時刻のタイムゾーン -->
        <div>
          <label for="departure_timezone" class="block text-sm font-medium text-gray-700">{{ __('出発時刻のタイムゾーン') }} <span class="text-red-500">*</span></label>
          <select 
            id="departure_timezone" 
            name="departure_timezone" 
            v-model="formData.departure_timezone"
            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
            required
          >
            <option 
              v-for="(label, value) in timezones" 
              :key="value" 
              :value="value"
            >
              {{ label }}
            </option>
          </select>
          <slot name="departure_timezone_error"></slot>
        </div>

        <!-- 到着時刻 -->
        <div>
          <label for="arrival_time" class="block text-sm font-medium text-gray-700">{{ __('到着時刻') }} <span class="text-red-500">*</span></label>
          <input 
            type="datetime-local" 
            name="arrival_time" 
            id="arrival_time" 
            v-model="formData.arrival_time"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
            required
          >
          <slot name="arrival_time_error"></slot>
        </div>

        <!-- 到着時刻のタイムゾーン -->
        <div>
          <label for="arrival_timezone" class="block text-sm font-medium text-gray-700">{{ __('到着時刻のタイムゾーン') }} <span class="text-red-500">*</span></label>
          <select 
            id="arrival_timezone" 
            name="arrival_timezone" 
            v-model="formData.arrival_timezone"
            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
            required
          >
            <option 
              v-for="(label, value) in timezones" 
              :key="value" 
              :value="value"
            >
              {{ label }}
            </option>
          </select>
          <slot name="arrival_timezone_error"></slot>
        </div>

        <!-- 会社名 -->
        <div>
          <label for="company_name" class="block text-sm font-medium text-gray-700">
            {{ __('会社名') }} 
            <span class="text-red-500" :class="{ 'hidden': !isFlightSelected }">*</span>
          </label>
          <input 
            type="text" 
            name="company_name" 
            id="company_name" 
            v-model="formData.company_name"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            :required="isFlightSelected"
          >
          <p class="mt-1 text-sm text-gray-500" :class="{ 'hidden': !isFlightSelected }">
            {{ __('飛行機の場合は必須です') }}
          </p>
          <slot name="company_name_error"></slot>
        </div>

        <!-- 便名・列車番号など -->
        <div>
          <label for="reference_number" class="block text-sm font-medium text-gray-700">
            {{ __('便名・列車番号など') }} 
            <span class="text-red-500" :class="{ 'hidden': !isFlightSelected }">*</span>
          </label>
          <input 
            type="text" 
            name="reference_number" 
            id="reference_number" 
            v-model="formData.reference_number"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            :required="isFlightSelected"
          >
          <p class="mt-1 text-sm text-gray-500" :class="{ 'hidden': !isFlightSelected }">
            {{ __('飛行機の場合は必須です') }}
          </p>
          <slot name="reference_number_error"></slot>
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
          ></member-selector>
          
          <slot name="member_ids_error"></slot>
        </div>
      </div>

      <div class="mt-6 flex justify-end">
        <slot name="cancel_button"></slot>
        <slot name="submit_button"></slot>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  props: {
    transportationTypes: {
      type: Array,
      required: true
    },
    timezones: {
      type: Object,
      required: true
    },
    defaultTimezone: {
      type: String,
      required: true
    },
    departureDate: {
      type: String,
      default: null
    },
    nextDay: {
      type: String,
      default: null
    },
    itinerary: {
      type: Object,
      default: null
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
    oldValues: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      formData: {
        transportation_type: this.getInitialValue('transportation_type', ''),
        departure_location: this.getInitialValue('departure_location', ''),
        arrival_location: this.getInitialValue('arrival_location', ''),
        departure_time: this.getInitialValue('departure_time', this.departureDate || ''),
        departure_timezone: this.getInitialValue('departure_timezone', this.defaultTimezone),
        arrival_time: this.getInitialValue('arrival_time', this.nextDay || ''),
        arrival_timezone: this.getInitialValue('arrival_timezone', this.defaultTimezone),
        company_name: this.getInitialValue('company_name', ''),
        reference_number: this.getInitialValue('reference_number', ''),
        notes: this.getInitialValue('notes', '')
      },
      isFlightSelected: false
    }
  },
  created() {
    // 初期表示時に飛行機が選択されているかチェック
    this.updateFlightFields();
  },
  methods: {
    // __ 関数の定義（Laravel Blade の __ 関数の代替）
    __(text) {
      return text; // 実際の翻訳機能が必要な場合は、ここを拡張
    },
    
    // 交通手段が変更された時の処理
    updateFlightFields() {
      this.isFlightSelected = this.formData.transportation_type === 'flight';
    },
    
    // 交通手段のラベルを取得
    getTransportationLabel(value) {
      const labels = {
        'flight': this.__('飛行機'),
        'train': this.__('電車'),
        'bus': this.__('バス'),
        'ferry': this.__('フェリー'),
        'car': this.__('車'),
        'walk': this.__('徒歩'),
        'bike': this.__('バイク')
      };
      
      return labels[value] || this.__('その他');
    },
    
    // 初期値を取得
    getInitialValue(field, defaultValue) {
      // oldValues（バリデーションエラー時の入力値）があればそれを優先
      if (this.oldValues && this.oldValues[field] !== undefined) {
        return this.oldValues[field];
      }
      
      // 編集時は既存の値を使用
      if (this.itinerary && this.itinerary[field] !== undefined) {
        return this.itinerary[field];
      }
      
      // デフォルト値を返す
      return defaultValue;
    }
  }
}
</script>
