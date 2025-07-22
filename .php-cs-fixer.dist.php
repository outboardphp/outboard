<?php

/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:3.83.0|configurator
 * you can change this configuration by importing this file.
 */
$config = new PhpCsFixer\Config();
return $config
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        // Each line of multi-line DocComments must have an asterisk [PSR-5] and must be aligned with the first one.
        'align_multiline_comment' => ['comment_type' => 'all_multiline'],
        // Converts simple usages of `array_push($x, $y);` to `$x[] = $y;`.
        'array_push' => true,
        // PHP attributes declared without arguments must (not) be followed by empty parentheses.
        'attribute_empty_parentheses' => true,
        // An empty line feed must precede any configured statement.
        'blank_line_before_statement' => ['statements' => ['declare','phpdoc']],
        // Class, trait and interface elements must be separated with one or none blank line.
        'class_attributes_separation' => true,
        // When referencing an internal class, it must be written using the correct casing.
        'class_reference_name_casing' => true,
        // Namespace must not contain spacing, comments or PHPDoc.
        'clean_namespace' => true,
        // Using `isset($var) &&` multiple times should be done in one call.
        'combine_consecutive_issets' => true,
        // Calling `unset` on multiple items should be done in one call.
        'combine_consecutive_unsets' => true,
        // Replace multiple nested calls of `dirname` by only one call with the second `$level` parameter.
        'combine_nested_dirname' => true,
        // Class `DateTimeImmutable` should be used instead of `DateTime`.
        'date_time_immutable' => true,
        // There must not be spaces around `declare` statement parentheses.
        'declare_parentheses' => true,
        // Replaces `dirname(__FILE__)` expression with equivalent `__DIR__` constant.
        'dir_constant' => true,
        // Replace deprecated `ereg` regular expression functions with `preg`.
        'ereg_to_preg' => true,
        // Add curly braces to indirect variables to make them clear to understand.
        'explicit_indirect_variable' => true,
        // Converts implicit variables into explicit ones in double-quoted strings or heredoc syntax.
        'explicit_string_variable' => true,
        // Order the flags in `fopen` calls, `b` and `t` must be last.
        'fopen_flag_order' => true,
        // The flags in `fopen` calls must omit `t`, and `b` must be omitted or included consistently.
        'fopen_flags' => true,
        // Replace core functions calls returning constants with the constants.
        'function_to_constant' => true,
        // Replace `get_class` calls on object variables with class keyword syntax.
        'get_class_to_class_keyword' => true,
        // Function `implode` must be called with 2 arguments in the documented order.
        'implode_call' => true,
        // Include/Require and the file path should be divided with a single space. File path should not be placed within parentheses.
        'include' => true,
        // Integer literals must be in the correct case.
        'integer_literal_case' => true,
        // Replaces `is_null($var)` expression with `null === $var`.
        'is_null' => true,
        // Lambda must not import variables it doesn't use.
        'lambda_not_used_import' => true,
        // Magic constants should be referred to using the correct casing.
        'magic_constant_casing' => true,
        // Magic method definitions and calls must be using the correct casing.
        'magic_method_casing' => true,
        // Replace `strpos()` and `stripos()` calls with `str_starts_with()` or `str_contains()` if possible.
        'modernize_strpos' => true,
        // Replaces `intval`, `floatval`, `doubleval`, `strval` and `boolval` function calls with according type casting operator.
        'modernize_types_casting' => true,
        // Promoted properties must be on separate lines.
        'multiline_promoted_properties' => true,
        // Add leading `\` before constant invocation of internal constant to speed up resolving. Constant name match is case-sensitive, except for `null`, `false` and `true`.
        'native_constant_invocation' => ['scope' => 'namespaced', 'strict' => false],
        // Function defined by PHP should be called using the correct casing.
        'native_function_casing' => true,
        // Add leading `\` before function invocation to speed up resolving.
        'native_function_invocation' => ['include' => ['@internal'], 'scope' => 'namespaced', 'strict' => false],
        // Native type declarations should be used in the correct case.
        'native_type_declaration_casing' => true,
        // All `new` expressions with a further call must (not) be wrapped in parentheses.
        'new_expression_parentheses' => true,
        // Original functions shall be used instead of aliases.
        'no_alias_functions' => true,
        // Replace control structure alternative syntax to use braces.
        'no_alternative_syntax' => true,
        // There should not be blank lines between a docblock and the documented element.
        'no_blank_lines_after_phpdoc' => true,
        // There should not be any empty comments.
        'no_empty_comment' => true,
        // There should not be empty PHPDoc blocks.
        'no_empty_phpdoc' => true,
        // Remove useless (semicolon) statements.
        'no_empty_statement' => true,
        // Replace accidental usage of homoglyphs (non-ascii characters) in names.
        'no_homoglyph_names' => true,
        // The namespace declaration line shouldn't contain leading whitespace.
        'no_leading_namespace_whitespace' => true,
        // Either language construct `print` or `echo` should be used.
        'no_mixed_echo_print' => true,
        // Operator `=>` should not be surrounded by multi-line whitespaces.
        'no_multiline_whitespace_around_double_arrow' => true,
        // Convert PHP4-style constructors to `__construct`.
        'no_php4_constructor' => true,
        // Short cast `bool` using double exclamation mark should not be used.
        'no_short_bool_cast' => true,
        // Single-line whitespace before closing semicolon is prohibited.
        'no_singleline_whitespace_before_semicolons' => true,
        // There MUST NOT be spaces around offset braces.
        'no_spaces_around_offset' => true,
        // Replaces superfluous `elseif` with `if`.
        'no_superfluous_elseif' => true,
        // Removes `@param`, `@return` and `@var` tags that don't provide any useful information.
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        // If a list of values separated by a comma is contained on a single line, then the last item MUST NOT have a trailing comma.
        'no_trailing_comma_in_singleline' => true,
        // Removes unneeded braces that are superfluous and aren't part of a control structure's body.
        'no_unneeded_braces' => true,
        // Removes unneeded parentheses around control statements.
        'no_unneeded_control_parentheses' => true,
        // Imports should not be aliased as the same name.
        'no_unneeded_import_alias' => true,
        // Variables must be set `null` instead of using `(unset)` casting.
        'no_unset_cast' => true,
        // Unused `use` statements must be removed.
        'no_unused_imports' => true,
        // There should not be useless `else` cases.
        'no_useless_else' => true,
        // There should not be a useless Null-safe operator `?->` used.
        'no_useless_nullsafe_operator' => true,
        // There should not be an empty `return` statement at the end of a function.
        'no_useless_return' => true,
        // There must be no `sprintf` calls with only the first argument.
        'no_useless_sprintf' => true,
        // In array declaration, there MUST NOT be a whitespace character before each comma.
        'no_whitespace_before_comma_in_array' => true,
        // Array index should always be written by using square braces.
        'normalize_index_brace' => true,
        // Nullable single type declaration should be standardized using configured syntax.
        'nullable_type_declaration' => true,
        // Adds or removes `?` before single type declarations or `|null` at the end of union types when parameters have a default `null` value.
        'nullable_type_declaration_for_default_null_value' => true,
        // Adds separators to numeric literals of any kind.
        'numeric_literal_separator' => true,
        // There should not be space before or after object operators `->` and `?->`.
        'object_operator_without_whitespace' => true,
        // Operators - when multiline - must always be at the beginning or at the end of the line.
        'operator_linebreak' => true,
        // Sorts attributes using the configured sort algorithm.
        'ordered_attributes' => true,
        // Docblocks should have the same indentation as the documented subject.
        'phpdoc_indent' => true,
        // Fixes PHPDoc inline tags.
        'phpdoc_inline_tag_normalizer' => true,
        // Changes doc blocks from single to multi line, or reversed. Works for class constants, properties and methods only.
        'phpdoc_line_span' => ['const' => 'single','property' => 'single'],
        // `@access` annotations must be removed from PHPDoc.
        'phpdoc_no_access' => true,
        // `@return void` and `@return null` annotations must be removed from PHPDoc.
        'phpdoc_no_empty_return' => true,
        // Classy that does not inherit must not have `@inheritdoc` tags.
        'phpdoc_no_useless_inheritdoc' => true,
        // Annotations in PHPDoc should be ordered in a defined sequence.
        'phpdoc_order' => true,
        // Orders all `@param` annotations in DocBlocks according to method signature.
        'phpdoc_param_order' => true,
        // Scalar types should always be written in the same form. `int` not `integer`, `bool` not `boolean`, `float` not `real` or `double`.
        'phpdoc_scalar' => true,
        // Single line `@var` PHPDoc should have proper spacing.
        'phpdoc_single_line_var_spacing' => true,
        // Fixes casing of PHPDoc tags.
        'phpdoc_tag_casing' => true,
        // Docblocks should only be used on structural elements.
        'phpdoc_to_comment' => true,
        // PHPDoc should start and end with content, excluding the very first and last line of the docblocks.
        'phpdoc_trim' => true,
        // Removes extra blank lines after summary and after description in PHPDoc.
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        // The correct case must be used for standard PHP types in PHPDoc.
        'phpdoc_types' => true,
        // `@var` and `@type` annotations must have type and name in the correct order.
        'phpdoc_var_annotation_correct_order' => true,
        // `@var` and `@type` annotations of classy properties should not contain the name.
        'phpdoc_var_without_name' => true,
        // Callables must be called without using `call_user_func*` when possible.
        'regular_callable_call' => true,
        // Local, dynamic and directly referenced variables should not be assigned and directly returned by a function or method.
        'return_assignment' => true,
        // If the function explicitly returns an array and has the return type `iterable`, then `yield from` must be used instead of `return`.
        'return_to_yield_from' => true,
        // Inside an enum or `final`/anonymous class, `self` should be preferred over `static`.
        'self_static_accessor' => true,
        // Instructions must be terminated with a semicolon.
        'semicolon_after_instruction' => true,
        // Converts explicit variables in double-quoted strings and heredoc syntax from simple to complex format (`${` to `{$`).
        'simple_to_complex_string_variable' => true,
        // Simplify `if` control structures that return the boolean result of their condition.
        'simplified_if_return' => true,
        // Single-line comments must have proper spacing.
        'single_line_comment_spacing' => true,
        // Convert double quotes to single quotes for simple strings.
        'single_quote' => true,
        // Replace all `<>` with `!=`.
        'standardize_not_equals' => true,
        // Handles implicit backslashes in strings and heredocs. Depending on the chosen strategy, it can escape implicit backslashes to ease the understanding of which are special chars interpreted by PHP and which not (`escape`), or it can remove these additional backslashes if you find them superfluous (`unescape`). You can also leave them as-is using `ignore` strategy.
        'string_implicit_backslashes' => true,
        // Arrays should be formatted like function/method arguments, without leading or trailing single line space.
        'trim_array_spaces' => true,
        // Ensure a single space between a variable and its type declaration in function arguments and properties.
        'type_declaration_spaces' => true,
        // A single space or none should be around union type and intersection type operators.
        'types_spaces' => true,
        // In array declaration, there MUST be a whitespace character after each comma.
        'whitespace_after_comma_in_array' => true,
        // Write conditions in Yoda style (`true`), non-Yoda style (`['equal' => false, 'identical' => false, 'less_and_greater' => false]`) or ignore those conditions (`null`) based on configuration.
        'yoda_style' => ['equal' => false,'identical' => false],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->ignoreVCSIgnored(true),
        // ->exclude([
        //     'folder-to-exclude',
        // ])
        // ->append([
        //     'file-to-include',
        // ])
    )
;
