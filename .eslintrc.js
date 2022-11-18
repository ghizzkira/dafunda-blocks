module.exports = {
  env: {
    browser: true,
    es2021: true,
  },
  extends: [
    "prettier",
    "eslint:recommended",
    "plugin:react/recommended",
    "plugin:prettier/recommended",
  ],
  overrides: [
    {
      files: ["*.js", "*.jsx", "*.mjs"],
      rules: {
        "no-undef": "off",
        "no-shadow": "off",
        "no-nested-ternary": "off",
        "no-tabs": "off",
        indent: "off",
        semi: "off",
        camelcase: "off",
        quotes: "off",
        eqeqeq: "off",
        "no-underscore-dangle": "off",
        "react/jsx-props-no-spreading": "off",
        "react/react-in-jsx-scope": "off",
        "react/prop-type": "off",
        "react/prop-types": "off",
        "react/no-array-index-key": "off",
        "import/no-extraneous-dependencies": "off",
        "import/prefer-default-export": "off",
        "import/no-cycle": "off",
        "import/no-named-as-default": "off",
        "import/no-named-as-default-member": "off",
        "react/jsx-filename-extension": "off",
        "jsx-a11y/alt-text": "off",
        "jsx-a11y/click-events-have-key-events": "off",
        "jsx-a11y/no-static-element-interactions": "off",
        "jsx-a11y/label-has-associated-control": "off",
        "prettier/prettier": "error",
        "no-unused-vars": [
          "error",
          {
            varsIgnorePattern: "^_",
            argsIgnorePattern: "^_",
          },
        ],
      },
    },
  ],
  parserOptions: {
    ecmaVersion: "latest",
    sourceType: "module",
  },
  plugins: ["react", "prettier"],
  rules: {},
};
