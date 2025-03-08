import { describe, it, expect, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import ItineraryForm from '../ItineraryForm.vue';
import MemberSelector from '../MemberSelector.vue';

// MemberSelectorコンポーネントをモック
vi.mock('../MemberSelector.vue', () => ({
  default: {
    name: 'MemberSelector',
    template: '<div class="mock-member-selector"></div>',
    props: ['branchGroups', 'members', 'initialSelectedMembers', 'inputName']
  }
}));

describe('ItineraryForm', () => {
  // テスト用のモックデータ
  const transportationTypes = [
    { value: 'flight', label: '飛行機' },
    { value: 'train', label: '電車' },
    { value: 'bus', label: 'バス' },
    { value: 'ferry', label: 'フェリー' },
    { value: 'car', label: '車' },
    { value: 'walk', label: '徒歩' },
    { value: 'bike', label: 'バイク' }
  ];
  
  const timezones = {
    'Asia/Tokyo': '東京 (UTC+9:00)',
    'Asia/Seoul': 'ソウル (UTC+9:00)',
    'America/New_York': 'ニューヨーク (UTC-5:00)'
  };
  
  const branchGroups = [
    { id: 1, name: 'グループA', members: [{ id: 1 }, { id: 2 }] }
  ];
  
  const members = [
    { id: 1, name: 'メンバー1' },
    { id: 2, name: 'メンバー2' }
  ];
  
  const defaultTimezone = 'Asia/Tokyo';
  const departureDate = '2025-04-01T10:00';
  const nextDay = '2025-04-02T10:00';
  const formAction = '/travel-plans/1/itineraries';
  
  // 基本的なマウントオプション
  const createWrapper = (props = {}) => {
    return mount(ItineraryForm, {
      props: {
        transportationTypes,
        timezones,
        defaultTimezone,
        departureDate,
        nextDay,
        branchGroups,
        members,
        selectedMemberIds: [],
        formAction,
        oldValues: {},
        ...props
      },
      global: {
        mocks: {
          __: (text) => text // 翻訳関数のモック
        },
        stubs: {
          MemberSelector: true
        }
      },
      slots: {
        csrf: '<input type="hidden" name="_token" value="test-token">',
        transportation_type_error: '<p class="error">エラーメッセージ</p>',
        departure_location_error: '<p class="error">エラーメッセージ</p>',
        arrival_location_error: '<p class="error">エラーメッセージ</p>',
        departure_time_error: '<p class="error">エラーメッセージ</p>',
        departure_timezone_error: '<p class="error">エラーメッセージ</p>',
        arrival_time_error: '<p class="error">エラーメッセージ</p>',
        arrival_timezone_error: '<p class="error">エラーメッセージ</p>',
        company_name_error: '<p class="error">エラーメッセージ</p>',
        reference_number_error: '<p class="error">エラーメッセージ</p>',
        notes_error: '<p class="error">エラーメッセージ</p>',
        member_ids_error: '<p class="error">エラーメッセージ</p>',
        cancel_button: '<button class="cancel">キャンセル</button>',
        submit_button: '<button type="submit">保存</button>'
      }
    });
  };

  it('コンポーネントが正しくレンダリングされる', () => {
    const wrapper = createWrapper();
    
    // フォームのaction属性が正しく設定されているか
    expect(wrapper.find('form').attributes('action')).toBe(formAction);
    
    // 交通手段のセレクトボックスが表示されているか
    expect(wrapper.find('select[name="transportation_type"]').exists()).toBe(true);
    
    // 必須フィールドが表示されているか
    expect(wrapper.find('input[name="departure_location"]').exists()).toBe(true);
    expect(wrapper.find('input[name="arrival_location"]').exists()).toBe(true);
    expect(wrapper.find('input[name="departure_time"]').exists()).toBe(true);
    expect(wrapper.find('select[name="departure_timezone"]').exists()).toBe(true);
    expect(wrapper.find('input[name="arrival_time"]').exists()).toBe(true);
    expect(wrapper.find('select[name="arrival_timezone"]').exists()).toBe(true);
    
    // 会社名と便名のフィールドが表示されているか
    expect(wrapper.find('input[name="company_name"]').exists()).toBe(true);
    expect(wrapper.find('input[name="reference_number"]').exists()).toBe(true);
    
    // メモフィールドが表示されているか
    expect(wrapper.find('textarea[name="notes"]').exists()).toBe(true);
    
    // MemberSelectorコンポーネントが表示されているか
    expect(wrapper.findComponent({ name: 'MemberSelector' }).exists()).toBe(true);
    
    // スロットが正しく表示されているか
    expect(wrapper.find('input[type="hidden"][name="_token"]').exists()).toBe(true);
    expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    expect(wrapper.find('button.cancel').exists()).toBe(true);
  });

  it('初期値が正しく設定される', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm;
    
    // 出発時刻と到着時刻の初期値が正しく設定されているか
    expect(vm.formData.departure_time).toBe(departureDate);
    expect(vm.formData.arrival_time).toBe(nextDay);
    
    // タイムゾーンの初期値が正しく設定されているか
    expect(vm.formData.departure_timezone).toBe(defaultTimezone);
    expect(vm.formData.arrival_timezone).toBe(defaultTimezone);
  });

  it('oldValuesから初期値が設定される', () => {
    const oldValues = {
      transportation_type: 'train',
      departure_location: '東京',
      arrival_location: '大阪',
      departure_time: '2025-04-01T12:00',
      departure_timezone: 'Asia/Tokyo',
      arrival_time: '2025-04-01T15:00',
      arrival_timezone: 'Asia/Tokyo',
      company_name: 'JR',
      reference_number: '123',
      notes: 'テストメモ'
    };
    
    const wrapper = createWrapper({ oldValues });
    const vm = wrapper.vm;
    
    // oldValuesの値が正しく設定されているか
    Object.keys(oldValues).forEach(key => {
      expect(vm.formData[key]).toBe(oldValues[key]);
    });
  });

  it('itineraryから初期値が設定される', () => {
    const itinerary = {
      transportation_type: 'bus',
      departure_location: '京都',
      arrival_location: '奈良',
      departure_time: '2025-04-02T09:00',
      departure_timezone: 'Asia/Tokyo',
      arrival_time: '2025-04-02T10:30',
      arrival_timezone: 'Asia/Tokyo',
      company_name: '奈良交通',
      reference_number: '456',
      notes: '観光バス'
    };
    
    const wrapper = createWrapper({ itinerary, oldValues: {} });
    const vm = wrapper.vm;
    
    // itineraryの値が正しく設定されているか
    Object.keys(itinerary).forEach(key => {
      expect(vm.formData[key]).toBe(itinerary[key]);
    });
  });

  it('oldValuesがitineraryより優先される', () => {
    const oldValues = {
      transportation_type: 'train',
      departure_location: '東京'
    };
    
    const itinerary = {
      transportation_type: 'bus',
      departure_location: '京都',
      arrival_location: '奈良'
    };
    
    const wrapper = createWrapper({ itinerary, oldValues });
    const vm = wrapper.vm;
    
    // oldValuesの値が優先されているか
    expect(vm.formData.transportation_type).toBe(oldValues.transportation_type);
    expect(vm.formData.departure_location).toBe(oldValues.departure_location);
    
    // oldValuesにない値はitineraryから取得されているか
    expect(vm.formData.arrival_location).toBe(itinerary.arrival_location);
  });

  it('交通手段が変更されると飛行機関連フィールドの必須状態が更新される', async () => {
    const wrapper = createWrapper();
    
    // 初期状態では飛行機が選択されていないので、会社名と便名は必須ではない
    expect(wrapper.vm.isFlightSelected).toBe(false);
    
    // 交通手段を飛行機に変更
    await wrapper.find('select[name="transportation_type"]').setValue('flight');
    
    // 飛行機が選択されたので、会社名と便名が必須になっているか
    expect(wrapper.vm.isFlightSelected).toBe(true);
    
    // 交通手段をバスに変更
    await wrapper.find('select[name="transportation_type"]').setValue('bus');
    
    // 飛行機が選択されていないので、会社名と便名は必須ではない
    expect(wrapper.vm.isFlightSelected).toBe(false);
  });

  it('getTransportationLabelメソッドが正しく動作する', () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm;
    
    // 各交通手段のラベルが正しく取得できるか
    expect(vm.getTransportationLabel('flight')).toBe('飛行機');
    expect(vm.getTransportationLabel('train')).toBe('電車');
    expect(vm.getTransportationLabel('bus')).toBe('バス');
    expect(vm.getTransportationLabel('ferry')).toBe('フェリー');
    expect(vm.getTransportationLabel('car')).toBe('車');
    expect(vm.getTransportationLabel('walk')).toBe('徒歩');
    expect(vm.getTransportationLabel('bike')).toBe('バイク');
    
    // 未定義の交通手段の場合はその他が返されるか
    expect(vm.getTransportationLabel('unknown')).toBe('その他');
  });

  it('getInitialValueメソッドが正しく動作する', () => {
    const oldValues = { field1: 'old' };
    const itinerary = { field1: 'itinerary', field2: 'itinerary' };
    const wrapper = createWrapper({ oldValues, itinerary });
    const vm = wrapper.vm;
    
    // oldValuesがある場合はそれが優先される
    expect(vm.getInitialValue('field1', 'default')).toBe('old');
    
    // oldValuesにない場合はitineraryから取得される
    expect(vm.getInitialValue('field2', 'default')).toBe('itinerary');
    
    // どちらにもない場合はデフォルト値が返される
    expect(vm.getInitialValue('field3', 'default')).toBe('default');
  });

  it('MemberSelectorコンポーネントに正しいプロパティが渡される', () => {
    const selectedMemberIds = [1, 2];
    const wrapper = createWrapper({ selectedMemberIds });
    
    const memberSelector = wrapper.findComponent({ name: 'MemberSelector' });
    
    // MemberSelectorに正しいプロパティが渡されているか
    expect(memberSelector.props('branchGroups')).toEqual(branchGroups);
    expect(memberSelector.props('members')).toEqual(members);
    expect(memberSelector.props('initialSelectedMembers')).toEqual(selectedMemberIds);
    expect(memberSelector.props('inputName')).toBe('member_ids');
  });
});
