/**
 * ESLint rule to prefer `MathClamp(v, min, max)` over nested
 * `Math.min(Math.max(...), ...)` / `Math.max(Math.min(...), ...)` patterns.
 *
 * Detected patterns and their fixes:
 *   Math.min(Math.max(A, B), C)  →  MathClamp(A, B, C)
 *   Math.min(C, Math.max(A, B))  →  MathClamp(A, B, C)
 *   Math.max(Math.min(A, B), C)  →  MathClamp(A, C, B)
 *   Math.max(C, Math.min(A, B))  →  MathClamp(A, C, B)
 */

function isMathCall(node, method) {
  return (
    node.type === "CallExpression" &&
    node.callee.type === "MemberExpression" &&
    !node.callee.computed &&
    node.callee.object.type === "Identifier" &&
    node.callee.object.name === "Math" &&
    node.callee.property.type === "Identifier" &&
    node.callee.property.name === method &&
    node.arguments.length === 2 &&
    node.arguments.every(a => a.type !== "SpreadElement")
  );
}

// Returns true if node is a Math.min or Math.max call.
function isMathMinMax(node) {
  return isMathCall(node, "min") || isMathCall(node, "max");
}

const preferMathClampRule = {
  meta: {
    type: "suggestion",
    fixable: "code",
    docs: {
      description:
        "Prefer MathClamp(v, min, max) over nested Math.min/Math.max",
    },
    messages: {
      useClamp:
        "Use MathClamp(v, min, max) instead of nested Math.min/Math.max.",
    },
    schema: [],
  },
  create(context) {
    const src = context.sourceCode ?? context.getSourceCode();

    return {
      CallExpression(node) {
        // Pattern: Math.min(Math.max(A, B), C) or Math.min(C, Math.max(A, B)).
        // Fix as MathClamp(A, B, C) where A,B are inner args, C is outer arg.
        if (isMathCall(node, "min")) {
          const [arg0, arg1] = node.arguments;
          let outerArg, innerNode;

          // Math.max(Math.min(A, B), Math.min(C, D)) isn't a clamp pattern, so
          // require the outer arg to not be a min/max call.
          if (isMathCall(arg0, "max") && !isMathMinMax(arg1)) {
            innerNode = arg0;
            outerArg = arg1;
          } else if (isMathCall(arg1, "max") && !isMathMinMax(arg0)) {
            innerNode = arg1;
            outerArg = arg0;
          } else {
            return;
          }

          const v = src.getText(innerNode.arguments[0]);
          const min = src.getText(innerNode.arguments[1]);
          const max = src.getText(outerArg);

          context.report({
            node,
            messageId: "useClamp",
            fix(fixer) {
              return fixer.replaceText(node, `MathClamp(${v}, ${min}, ${max})`);
            },
          });
        }

        // Pattern: Math.max(Math.min(A, B), C) or Math.max(C, Math.min(A, B)).
        // Fix as MathClamp(A, C, B) where A,B are inner args, C is outer arg.
        if (isMathCall(node, "max")) {
          const [arg0, arg1] = node.arguments;
          let outerArg, innerNode;

          if (isMathCall(arg0, "min") && !isMathMinMax(arg1)) {
            innerNode = arg0;
            outerArg = arg1;
          } else if (isMathCall(arg1, "min") && !isMathMinMax(arg0)) {
            innerNode = arg1;
            outerArg = arg0;
          } else {
            return;
          }

          const v = src.getText(innerNode.arguments[0]);
          const max = src.getText(innerNode.arguments[1]);
          const min = src.getText(outerArg);

          context.report({
            node,
            messageId: "useClamp",
            fix(fixer) {
              return fixer.replaceText(node, `MathClamp(${v}, ${min}, ${max})`);
            },
          });
        }
      },
    };
  },
};

export default {
  rules: {
    "prefer-math-clamp": preferMathClampRule,
  },
};
