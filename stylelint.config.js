module.exports = {
  extends: ['stylelint-config-standard'],
  rules: {
    'media-feature-range-notation': 'prefix'
  },
  overrides: [
    {
      files: ['**/*.less'],
      customSyntax: 'postcss-less'
    }
  ]
};
