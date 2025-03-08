import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import ExpenseForm from './ExpenseForm.vue'

// MemberSelectorコンポーネントをモック
vi.mock('./MemberSelector.vue', () => ({
  default: {
    name: 'MemberSelector',
    template: '<div class="mock-member-selector"></div>',
    props: ['branchGroups', 'members', 'initialSelectedMembers', 'inputName'],
    emits: ['update:selected']
  }
}))

describe('ExpenseForm', () => {
  it('メンバーが選択され金額が入力されると、メンバーごとの支払い金額フォームが表示される', async () => {
    // テスト用のデータを準備
    const props = {
      travelPlan: { id: 1 },
      branchGroups: [],
      members: [
        { id: 1, name: 'メンバー1' },
        { id: 2, name: 'メンバー2' }
      ],
      selectedMemberIds: [],
      formAction: '/test',
      cancelUrl: '/cancel',
      currencies: { JPY: '日本円', USD: '米ドル' }
    }

    // メタタグをモック
    document.head.innerHTML = '<meta name="user-id" content="1">';

    // コンポーネントをマウント
    const wrapper = mount(ExpenseForm, {
      props,
      global: {
        stubs: {
          MemberSelector: true
        }
      }
    })

    // 初期状態では支払い金額フォームは表示されていない
    // 注: selectedMembersList が空の場合、v-if="selectedMembersList.length > 0" の条件により表示されない
    expect(wrapper.findAll('.md-col-span-2 h3').length).toBe(0)

    // 金額を入力
    await wrapper.find('#amount').setValue(1000)

    // メンバーを選択（MemberSelectorからのイベントをシミュレート）
    await wrapper.vm.updateSelectedMembers([1, 2])
    await wrapper.vm.$nextTick() // DOMの更新を待つ

    // 支払い金額フォームが表示されていることを確認
    expect(wrapper.html()).toContain('メンバーごとの支払い金額')
  })

  it('班グループのメンバーが選択されると、メンバーごとの支払い金額フォームが表示される', async () => {
    // テスト用のデータを準備
    const props = {
      travelPlan: { id: 1 },
      branchGroups: [
        {
          id: 1,
          name: 'テストグループ1',
          members: [{ id: 1, name: 'メンバー1' }, { id: 2, name: 'メンバー2' }]
        }
      ],
      members: [
        { id: 1, name: 'メンバー1' },
        { id: 2, name: 'メンバー2' },
        { id: 3, name: 'メンバー3' }
      ],
      selectedMemberIds: [],
      formAction: '/test',
      cancelUrl: '/cancel',
      currencies: { JPY: '日本円', USD: '米ドル' }
    }

    // メタタグをモック
    document.head.innerHTML = '<meta name="user-id" content="1">';

    // コンポーネントをマウント
    const wrapper = mount(ExpenseForm, {
      props,
      global: {
        stubs: {
          MemberSelector: true
        }
      }
    })

    // 金額を入力
    await wrapper.find('#amount').setValue(1000)

    // 初期状態では支払い金額フォームは表示されていない
    // 注: selectedMembersList が空の場合、v-if="selectedMembersList.length > 0" の条件により表示されない
    expect(wrapper.findAll('.md-col-span-2 h3').length).toBe(0)

    // MemberSelectorからのイベントをシミュレート（班グループのメンバーが選択された）
    await wrapper.vm.updateSelectedMembers([1, 2])
    await wrapper.vm.$nextTick() // DOMの更新を待つ

    // 支払い金額フォームが表示されていることを確認
    expect(wrapper.html()).toContain('メンバーごとの支払い金額')
  })
})
