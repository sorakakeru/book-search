/**
 * book-search
 * https://github.com/sorakakeru/book-search
 * 
 * Copyright (c) 2025 Yamatsu
 * Released under the MIT license
 * https://github.com/sorakakeru/book-search/blob/main/LICENSE
 */

const form = document.querySelector('.form_area.register form')
const inputCsv = document.getElementById('csv_file')
const inputIsbn = document.getElementById('isbn')
const inputTitle = document.getElementById('title')
const inputAuthor = document.getElementById('author')
const inputPublisher = document.getElementById('publisher')
const checkEbook = document.getElementById('ebook')

/**
 * 入力フォームバリデーションチェック
 */

form.addEventListener('submit', (e) => {
  const eText = document.querySelectorAll('.form_area.register .error')
  eText.forEach(function(txt, i) { txt.remove() })
  document.querySelector('.success') && document.querySelector('.success').remove()

  if (inputCsv.value.length === 0 && inputTitle.value.length === 0) { //CSVファイルの指定もタイトルの入力もない場合
    form.insertAdjacentHTML('beforebegin', '<p class="error">CSVファイルの指定またはタイトルは入力必須です</p>')
    e.preventDefault()
  } else { //CSVファイルまたはタイトルが入力されている場合

    if (inputCsv.value.length > 0 && inputCsv.files[0].size > 1048576) { //CSVのファイルサイズが1MBを超える場合
      form.querySelector('dl').insertAdjacentHTML('beforebegin', '<p class="error">CSVファイルは1MBまでしかアップロードできません</p>')
      e.preventDefault()
    }

    if (inputCsv.value.length === 0) {

      // チェックボックスがオフまたはオンでISBNコード入力ありの場合は検証
      if (!checkEbook.checked || (checkEbook.checked && inputIsbn.value.length > 0)) {
        if (isNaN(inputIsbn.value) || inputIsbn.value.length !== 13) {
          e.preventDefault()
          inputIsbn.closest('dd').insertAdjacentHTML('beforeend', '<p class="error">ISBNコードは13桁の数字で入力してください</p>')
        }
      }

    }

  }
})
