{{- define "ingress.template" -}}
apiVersion: extensions/v1beta1
kind: Ingress
metadata:
  name: {{ .service.name | quote }}
spec:
  {{- if .service.tls }}
  tls:
  {{- range .service.tls }}
    - hosts:
      {{- range .hosts }}
        - {{ . | quote }}
      {{- end }}
      secretName: {{ .secretName }}
  {{- end }}
  {{- end }}
  rules:
  {{- range .service.hosts }}
    - http:
        paths:
        {{- range .paths }}
          - path: {{ . }}
            backend:
              serviceName: {{ .backend | quote }}
              servicePort: {{ .port }}
        {{- end }}
  {{- end }}
{{- end -}}
