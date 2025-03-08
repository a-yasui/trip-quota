import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import MemberSelector from './MemberSelector.vue'

describe('MemberSelector', () => {
  it('選択した班グループのメンバーが正しく選択される', async () => {
    // テスト用のデータを準備
    const branchGroups = [
      {
        id: 1,
        name: 'テストグループ1',
        members: [{ id: 1, name: 'メンバー1' }, { id: 2, name: 'メンバー2' }]
      }
    ]
    const members = [
      { id: 1, name: 'メンバー1' },
      { id: 2, name: 'メンバー2' },
      { id: 3, name: 'メンバー3' }
    ]

    // コンポーネントをマウント
    const wrapper = mount(MemberSelector, {
      props: {
        branchGroups,
        members,
        initialSelectedMembers: [],
        inputName: 'member_ids'
      }
    })

    // グループのチェックボックスをクリック
    const checkbox = wrapper.find('#group_1')
    await checkbox.setValue(true)
    await wrapper.vm.$nextTick()
    
    // 複数の非同期処理を待つ
    await new Promise(resolve => setTimeout(resolve, 0))
    await wrapper.vm.$nextTick()

    // グループに所属するメンバーが選択されていることを確認
    expect(wrapper.vm.selectedMembers[1]).toBe(true)
    expect(wrapper.vm.selectedMembers[2]).toBe(true)
    expect(wrapper.vm.selectedMembers[3]).toBeFalsy()

    // 親コンポーネントにイベントが発行されたことを確認
    const emittedEvents = wrapper.emitted('update:selected')
    expect(emittedEvents).toBeTruthy()
    expect(emittedEvents[emittedEvents.length - 1][0]).toEqual(expect.arrayContaining([1, 2]))
  })

  it('班グループのチェックボックスをクリックすると親コンポーネントに通知される', async () => {
    // テスト用のデータを準備
    const branchGroups = [
      {
        id: 1,
        name: 'テストグループ1',
        members: [{ id: 1, name: 'メンバー1' }, { id: 2, name: 'メンバー2' }]
      }
    ]
    const members = [
      { id: 1, name: 'メンバー1' },
      { id: 2, name: 'メンバー2' },
      { id: 3, name: 'メンバー3' }
    ]

    // コンポーネントをマウント
    const wrapper = mount(MemberSelector, {
      props: {
        branchGroups,
        members,
        initialSelectedMembers: [],
        inputName: 'member_ids'
      }
    })

    // グループのチェックボックスをクリック
    const checkbox = wrapper.find('#group_1')
    await checkbox.setValue(true)
    await wrapper.vm.$nextTick()
    
    // 複数の非同期処理を待つ
    await new Promise(resolve => setTimeout(resolve, 0))
    await wrapper.vm.$nextTick()

    // 親コンポーネントにイベントが発行されたことを確認
    const emittedEvents = wrapper.emitted('update:selected')
    expect(emittedEvents).toBeTruthy()
    expect(emittedEvents[emittedEvents.length - 1][0]).toEqual(expect.arrayContaining([1, 2]))
  })
})
