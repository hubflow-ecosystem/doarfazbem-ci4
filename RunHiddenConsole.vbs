If WScript.Arguments.Count >= 1 Then
    ReDim arr(WScript.Arguments.Count-1)
    For i = 0 To WScript.Arguments.Count-1
        arr(i) = WScript.Arguments(i)
    Next

    CreateObject("WScript.Shell").Run Join(arr), 0, False
End If
